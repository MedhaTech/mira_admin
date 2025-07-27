<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once 'db_connect.php';

$bot_id = isset($_GET['bot_id']) ? intval($_GET['bot_id']) : 0;
if (!$bot_id) {
    echo json_encode(['success' => false, 'message' => 'No bot_id provided']);
    exit;
}

// Function to categorize and group similar questions
function categorizeQuestions($userMessages) {
    $categories = [
        'greetings' => ['hi', 'hello', 'hey', 'good morning', 'good afternoon', 'good evening'],
        'identity' => ['who are you', 'what are you', 'tell me about yourself', 'your name'],
        'company_info' => ['medha tech', 'medha', 'company', 'about company', 'what is medha'],
        'services' => ['services', 'what do you do', 'offer', 'provide', 'help'],
        'contact' => ['contact', 'phone', 'email', 'address', 'location', 'reach'],
        'pricing' => ['price', 'cost', 'fee', 'charge', 'how much'],
        'support' => ['help', 'support', 'assist', 'problem', 'issue']
    ];
       
    $category_counts = [];
    $uncategorized = [];
    
    foreach ($userMessages as $msg) {
        $msg_lower = strtolower(trim($msg));
        $categorized = false;
        
        foreach ($categories as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($msg_lower, $keyword) !== false) {
                    $category_counts[$category] = ($category_counts[$category] ?? 0) + 1;
                    $categorized = true;
                    break 2;
                }
            }
        }
        
        if (!$categorized && !empty($msg_lower)) {
            $uncategorized[] = $msg;
        }
    }
    
    // Create meaningful question labels
    $question_labels = [
        'greetings' => 'Greetings/Hello',
        'identity' => 'Who are you?',
        'company_info' => 'What is Medha Tech?',
        'services' => 'What services do you offer?',
        'contact' => 'How can I contact you?',
        'pricing' => 'What are your prices?',
        'support' => 'Can you help me?'
    ];
    
    $questions = [];
    
    // Add categorized questions
    foreach ($category_counts as $category => $count) {
        if (isset($question_labels[$category])) {
            $questions[] = [
                'question' => $question_labels[$category],
                'count' => $count
            ];
        }
    }
    
    // Add some uncategorized questions (limit to 2)
    $uncategorized_counts = array_count_values($uncategorized);
    arsort($uncategorized_counts);
    $top_uncategorized = array_slice($uncategorized_counts, 0, 2, true);
    
    foreach ($top_uncategorized as $question => $count) {
        $questions[] = [
            'question' => $question,
            'count' => $count
        ];
    }
    
    // Sort by count and return top 5
    usort($questions, function($a, $b) {
        return $b['count'] - $a['count'];
    });
    
    return array_slice($questions, 0, 5);
}

try {
    // Initialize default values
    $conv_stats = [
        'total_conversations' => 0,
        'total_conversations_today' => 0,
        'avg_messages_per_conversation' => 0,
        'longest_conversation' => 0,
        'last_conversation' => null,
        'total_messages' => 0
    ];
    
    $total_messages_today = 0;
    $avg_conversation_time = 'N/A';
    $top_questions = [];
    
    // Support query statistics
    $query_stats = [
        'total_queries' => 0,
        'queries_today' => 0,
        'top_category' => 'N/A',
        'avg_queries_per_day' => 0,
        'first_query' => 'N/A',
        'last_query' => 'N/A',
        'most_active_query_hour' => 'N/A'
    ];
    
    // Check if conversations table exists
    $table_check = $conn->query("SHOW TABLES LIKE 'conversations'");
    if ($table_check && $table_check->num_rows > 0) {
        
        // Get conversation statistics
        $stmt = $conn->prepare('SELECT 
            COUNT(*) as total_conversations,
            COUNT(CASE WHEN DATE(first_message_at) = CURDATE() THEN 1 END) as total_conversations_today,
            AVG(total_messages) as avg_messages_per_conversation,
            MAX(total_messages) as longest_conversation,
            MAX(last_message_at) as last_conversation,
            SUM(total_messages) as total_messages
            FROM conversations WHERE bot_id = ?');
        if ($stmt) {
            $stmt->bind_param('i', $bot_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result) {
                $conv_stats = $result->fetch_assoc();
            }
        }
        
        // Get total messages today
        $stmt = $conn->prepare('SELECT COUNT(*) as total_messages_today 
            FROM conversation_messages cm 
            JOIN conversations c ON cm.conversation_id = c.id 
            WHERE c.bot_id = ? AND DATE(cm.created_at) = CURDATE()');
        if ($stmt) {
            $stmt->bind_param('i', $bot_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result) {
                $row = $result->fetch_assoc();
                $total_messages_today = $row['total_messages_today'] ?? 0;
            }
        }
        
        // Calculate average conversation time more accurately
        $stmt = $conn->prepare('SELECT 
            c.id,
            c.first_message_at,
            c.last_message_at,
            TIMESTAMPDIFF(SECOND, c.first_message_at, c.last_message_at) as duration_seconds
            FROM conversations c 
            WHERE c.bot_id = ? AND c.first_message_at != c.last_message_at AND c.total_messages > 1');
        if ($stmt) {
            $stmt->bind_param('i', $bot_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result) {
                $total_seconds = 0;
                $conversation_count = 0;
                
                while ($row = $result->fetch_assoc()) {
                    $duration = $row['duration_seconds'];
                    // Only count conversations that lasted at least 5 seconds (to filter out very quick exchanges)
                    if ($duration >= 5) {
                        $total_seconds += $duration;
                        $conversation_count++;
                    }
                }
                
                if ($conversation_count > 0) {
                    $avg_time_seconds = $total_seconds / $conversation_count;
                    
                    if ($avg_time_seconds < 60) {
                        $avg_conversation_time = round($avg_time_seconds) . ' seconds';
                    } elseif ($avg_time_seconds < 3600) {
                        $avg_conversation_time = round($avg_time_seconds / 60) . ' minutes';
                    } else {
                        $hours = floor($avg_time_seconds / 3600);
                        $minutes = round(($avg_time_seconds % 3600) / 60);
                        $avg_conversation_time = $hours . 'h ' . $minutes . 'm';
                    }
                } else {
                    $avg_conversation_time = 'Less than 1 minute';
                }
            }
        }
        
        // Get user messages for analysis
        $stmt = $conn->prepare('SELECT cm.message_text 
            FROM conversation_messages cm 
            JOIN conversations c ON cm.conversation_id = c.id 
            WHERE c.bot_id = ? AND cm.message_type = "user"');
        if ($stmt) {
            $stmt->bind_param('i', $bot_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result) {
                $user_messages = [];
                while ($row = $result->fetch_assoc()) {
                    $user_messages[] = $row['message_text'];
                }
                
                // Use the categorization function
                $top_questions = categorizeQuestions($user_messages);
            }
        }
    }
    
    // Get support query statistics
    $stmt = $conn->prepare('SELECT COUNT(*) as total_queries FROM support_queries WHERE bot_id = ?');
    if ($stmt) {
        $stmt->bind_param('i', $bot_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result) {
            $row = $result->fetch_assoc();
            $query_stats['total_queries'] = $row['total_queries'] ?? 0;
        }
    }
    
    // Get today's queries
    $stmt = $conn->prepare('SELECT COUNT(*) as queries_today FROM support_queries WHERE bot_id = ? AND DATE(created_at) = CURDATE()');
    if ($stmt) {
        $stmt->bind_param('i', $bot_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result) {
            $row = $result->fetch_assoc();
            $query_stats['queries_today'] = $row['queries_today'] ?? 0;
        }
    }
    
    // Get top query category
    $stmt = $conn->prepare('SELECT category, COUNT(*) as count FROM support_queries WHERE bot_id = ? GROUP BY category ORDER BY count DESC LIMIT 1');
    if ($stmt) {
        $stmt->bind_param('i', $bot_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result) {
            $row = $result->fetch_assoc();
            if ($row) {
                $query_stats['top_category'] = $row['category'] ?: 'General';
            }
        }
    }
    
    // Get first and last query dates
    $stmt = $conn->prepare('SELECT MIN(created_at) as first_query, MAX(created_at) as last_query FROM support_queries WHERE bot_id = ?');
    if ($stmt) {
        $stmt->bind_param('i', $bot_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result) {
            $row = $result->fetch_assoc();
            if ($row['first_query']) {
                $query_stats['first_query'] = date('M d, Y', strtotime($row['first_query']));
            }
            if ($row['last_query']) {
                $query_stats['last_query'] = date('M d, Y', strtotime($row['last_query']));
            }
        }
    }
    
    // Get most active query hour
    $stmt = $conn->prepare('SELECT HOUR(created_at) as hour, COUNT(*) as count FROM support_queries WHERE bot_id = ? GROUP BY hour ORDER BY count DESC LIMIT 1');
    if ($stmt) {
        $stmt->bind_param('i', $bot_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result) {
            $row = $result->fetch_assoc();
            if ($row) {
                $query_stats['most_active_query_hour'] = date('g A', mktime($row['hour']));
            }
        }
    }
    
    // Calculate average queries per day
    if ($query_stats['first_query'] !== 'N/A' && $query_stats['last_query'] !== 'N/A') {
        $first_date = date('Y-m-d', strtotime($query_stats['first_query']));
        $last_date = date('Y-m-d', strtotime($query_stats['last_query']));
        $days_span = max(1, (strtotime($last_date) - strtotime($first_date)) / 86400 + 1);
        $query_stats['avg_queries_per_day'] = round($query_stats['total_queries'] / $days_span, 1);
    }
    
    // Format last conversation
    $last_conversation = 'No conversations yet';
    if ($conv_stats['last_conversation']) {
        $last_conversation = date('M d, Y g:i A', strtotime($conv_stats['last_conversation']));
    }
    
    $report = [
        'total_conversations_today' => intval($conv_stats['total_conversations_today'] ?? 0),
        'total_conversations' => intval($conv_stats['total_conversations'] ?? 0),
        'avg_messages_per_conversation' => round(floatval($conv_stats['avg_messages_per_conversation'] ?? 0), 1),
        'longest_conversation' => intval($conv_stats['longest_conversation'] ?? 0),
        'avg_conversation_time' => $avg_conversation_time,
        'total_messages_today' => intval($total_messages_today),
        'last_conversation' => $last_conversation,
        'top_questions' => $top_questions,
        'query_stats' => $query_stats
    ];
    
    echo json_encode(['success' => true, 'report' => $report]);
    
} catch (Exception $e) {
    // Return a basic report even if there's an error
    $report = [
        'total_conversations_today' => 0,
        'total_conversations' => 0,
        'avg_messages_per_conversation' => 0,
        'longest_conversation' => 0,
        'avg_conversation_time' => 'N/A',
        'total_messages_today' => 0,
        'last_conversation' => 'No conversations yet',
        'top_questions' => [],
        'query_stats' => [
            'total_queries' => 0,
            'queries_today' => 0,
            'top_category' => 'N/A',
            'avg_queries_per_day' => 0,
            'first_query' => 'N/A',
            'last_query' => 'N/A',
            'most_active_query_hour' => 'N/A'
        ]
    ];
    echo json_encode(['success' => true, 'report' => $report]);
}
?> 