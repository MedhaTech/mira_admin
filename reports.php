<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}
$username = htmlspecialchars($_SESSION['user']);

include_once 'db_connect.php';
// Get user ID
$stmt = $conn->prepare('SELECT id FROM users WHERE username = ?');
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();
$userRow = $result->fetch_assoc();
$user_id = $userRow['id'];

// Get all bots for this user
$stmt = $conn->prepare('SELECT * FROM bots WHERE user_id = ?');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$bots = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bot Reports - Mira Chat Bot</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background: #e6f0ff; }
        .topbar {
            width: 100%;
            background: #1565c0;
            color: #fff;
            padding: 0.8rem 2rem;
            font-size: 1.4rem;
            font-weight: bold;
            letter-spacing: 1px;
            box-shadow: 0 2px 8px rgba(21,101,192,0.08);
            position: fixed;
            top: 0;
            left: 0;
            z-index: 100;
        }
        .topbar .logo-link {
            color: #fff;
            text-decoration: none;
            font-size: 1.4rem;
            font-weight: bold;
            letter-spacing: 1px;
            transition: opacity 0.15s;
        }
        .topbar .logo-link:hover {
            opacity: 0.7;
        }
        .sidenav {
            position: fixed;
            top: 56px;
            left: 0;
            width: 180px;
            height: calc(100vh - 56px);
            background: #fff;
            box-shadow: 2px 0 8px rgba(21,101,192,0.07);
            display: flex;
            flex-direction: column;
            padding-top: 2rem;
        }
        .sidenav a, .sidenav form button {
            color: #1565c0;
            text-decoration: none;
            font-size: 1.08rem;
            background: none;
            border: none;
            cursor: pointer;
            font-family: inherit;
            padding: 0.7rem 1.5rem;
            border-radius: 4px;
            margin: 0.2rem 0;
            text-align: left;
            transition: background 0.13s;
        }
        .sidenav a:hover, .sidenav form button:hover {
            background: #e6f0ff;
        }
        .main {
            margin-left: 200px;
            margin-top: 70px;
            max-width: 1200px;
            padding: 2rem;
        }
        h2 { color: #1565c0; margin-bottom: 2rem; }
        .bot-list {
            display: flex;
            flex-wrap: wrap;
            gap: 2rem;
        }
        .bot-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(21,101,192,0.1);
            padding: 1.5rem;
            width: 250px;
            text-align: center;
            position: relative;
            transition: box-shadow 0.2s, transform 0.2s;
        }
        .bot-card:hover {
            box-shadow: 0 6px 18px rgba(21,101,192,0.18);
            transform: translateY(-4px) scale(1.03);
        }
        .bot-card img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 50%;
            margin-bottom: 1rem;
            background: #f0f4fa;
        }
        .bot-card h3 { margin: 0.5rem 0 0.2rem; color: #1565c0; }
        .bot-card .color-swatches {
            margin: 0.5rem 0;
        }
        .bot-card .color-swatches span {
            display:inline-block;width:18px;height:18px;border-radius:3px;margin-right:4px;
        }
        .show-report-btn {
            background: #1565c0;
            color: #fff;
            border: none;
            padding: 0.7rem 1.5rem;
            border-radius: 6px;
            font-size: 1rem;
            margin-top: 1rem;
            cursor: pointer;
            transition: background 0.2s;
            width: 100%;
        }
        .show-report-btn:hover { background: #003c8f; }
        .report-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .report-content {
            background-color: #fff;
            margin: 2% auto;
            padding: 2rem;
            border-radius: 15px;
            width: 90%;
            max-width: 1000px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover { color: #000; }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }
        .stat-card {
            background: linear-gradient(135deg, #f9fbff 0%, #e6f0ff 100%);
            padding: 1.5rem;
            border-radius: 12px;
            text-align: center;
            border: 1px solid #e6f0ff;
            box-shadow: 0 4px 12px rgba(21,101,192,0.1);
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-2px);
        }
        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: #1565c0;
            margin-bottom: 0.5rem;
        }
        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }
        .chart-section {
            margin-top: 2rem;
            background: linear-gradient(135deg, #f8faff 0%, #f0f8ff 100%);
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(21,101,192,0.08);
        }
        .chart-title {
            color: #1565c0;
            font-size: 1.3rem;
            font-weight: bold;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        .chart-container {
            position: relative;
            height: 300px;
            margin: 1rem 0;
        }
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 2rem;
            margin-top: 1.5rem;
        }
        .top-questions {
            margin-top: 2rem;
            background: linear-gradient(135deg, #f9fbff 0%, #f0f8ff 100%);
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(21,101,192,0.1);
        }
        .top-questions h3 {
            color: #1565c0;
            margin-bottom: 1rem;
            text-align: center;
        }
        .question-item {
            background: #fff;
            padding: 1rem;
            margin: 0.5rem 0;
            border-radius: 8px;
            border-left: 4px solid #1565c0;
            box-shadow: 0 2px 8px rgba(21,101,192,0.1);
            transition: transform 0.2s;
        }
        .question-item:hover {
            transform: translateX(5px);
        }
        .query-analytics {
            margin-top: 1.5rem;
            padding: 1.5rem;
            background: linear-gradient(135deg, #f0f8ff 0%, #e6f0ff 100%);
            border-radius: 12px;
            border-left: 4px solid #1565c0;
            box-shadow: 0 4px 12px rgba(21,101,192,0.1);
        }
        .query-analytics h3 {
            color: #1565c0;
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }
        .query-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
        }
        .query-stat {
            text-align: center;
            padding: 1rem;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(21,101,192,0.1);
        }
        .query-stat-label {
            font-weight: 600;
            color: #1565c0;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        .query-stat-value {
            font-size: 1.2rem;
            font-weight: bold;
            color: #003c8f;
        }
        @media (max-width: 900px) {
            .main { padding: 1rem; margin-left: 0; }
            .bot-list { gap: 1rem; }
            .bot-card { width: 100%; }
            .sidenav { display: none; }
            .report-content { width: 95%; margin: 5% auto; }
            .charts-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="topbar"><a href="index.php" class="logo-link">MiraChatBot</a></div>
    <div class="sidenav">
        <a href="dashboard.php">Dashboard</a>
        <a href="create_bot.php">Create New Bot</a>
        <a href="reports.php" style="background:#e6f0ff;font-weight:bold;">Reports</a>
        <form method="post" action="logout.php" style="margin:0;">
            <button type="submit">Logout</button>
        </form>
    </div>
    <div class="main">
        <h2>Bot Reports</h2>
        <div class="bot-list">
            <?php while ($bot = $bots->fetch_assoc()): ?>
                <div class="bot-card">
                    <img src="<?php echo htmlspecialchars($bot['logo'] ?: 'default_logo.png'); ?>" alt="Bot Logo">
                    <h3><?php echo htmlspecialchars($bot['name']); ?></h3>
                    <div class="color-swatches">
                        <span style="background:<?php echo htmlspecialchars($bot['primary_color']); ?>;"></span>
                        <span style="background:<?php echo htmlspecialchars($bot['secondary_color']); ?>;"></span>
                    </div>
                    <button class="show-report-btn" onclick="showReport(<?php echo $bot['id']; ?>, '<?php echo htmlspecialchars($bot['name']); ?>')">Show Report</button>
                </div>
            <?php endwhile; ?>
            <?php if ($bots->num_rows === 0): ?>
                <p>You have no bots yet. Create one to see reports!</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Report Modal -->
    <div id="reportModal" class="report-modal">
        <div class="report-content">
            <span class="close" onclick="closeReport()">&times;</span>
            <h2 id="reportTitle">Bot Report</h2>
            <div id="reportData">
                <div style="text-align: center; padding: 2rem;">
                    <div style="color: #666;">Loading report...</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showReport(botId, botName) {
            document.getElementById('reportTitle').textContent = botName + ' - Report';
            document.getElementById('reportModal').style.display = 'block';
            
            // Load report data
            fetch('get_bot_report.php?bot_id=' + botId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayReport(data.report);
                    } else {
                        document.getElementById('reportData').innerHTML = '<div style="color: #c62828; text-align: center; padding: 2rem;">Error loading report: ' + data.message + '</div>';
                    }
                })
                .catch(error => {
                    document.getElementById('reportData').innerHTML = '<div style="color: #c62828; text-align: center; padding: 2rem;">Error loading report</div>';
                });
        }

        function closeReport() {
            document.getElementById('reportModal').style.display = 'none';
        }

        function displayReport(report) {
            const html = `
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-value">${report.total_conversations_today}</div>
                        <div class="stat-label">Total Conversations Today</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">${report.total_conversations}</div>
                        <div class="stat-label">Total Conversations Overall</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">${report.avg_messages_per_conversation}</div>
                        <div class="stat-label">Avg Messages per Conversation</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">${report.longest_conversation}</div>
                        <div class="stat-label">Longest Conversation</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">${report.avg_conversation_time}</div>
                        <div class="stat-label">Average Conversation Time</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">${report.total_messages_today}</div>
                        <div class="stat-label">Total Messages Today</div>
                    </div>
                </div>
                
                <div style="margin-top: 1.5rem; padding: 1rem; background: linear-gradient(135deg, #f9fbff 0%, #e6f0ff 100%); border-radius: 12px; box-shadow: 0 4px 12px rgba(21,101,192,0.1);">
                    <div style="font-weight: 600; color: #1565c0; margin-bottom: 0.5rem;">Last Conversation</div>
                    <div>${report.last_conversation}</div>
                </div>
                
                <div class="chart-section">
                    <div class="chart-title">📊 Activity Overview</div>
                    <div class="charts-grid">
                        <div>
                            <div class="chart-container">
                                <canvas id="conversationChart"></canvas>
                            </div>
                        </div>
                        <div>
                            <div class="chart-container">
                                <canvas id="queryChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="query-analytics">
                    <h3>📋 Support Query Analytics</h3>
                    <div class="query-grid">
                        <div class="query-stat">
                            <div class="query-stat-label">Total Queries</div>
                            <div class="query-stat-value">${report.query_stats.total_queries}</div>
                        </div>
                        <div class="query-stat">
                            <div class="query-stat-label">Queries Today</div>
                            <div class="query-stat-value">${report.query_stats.queries_today}</div>
                        </div>
                        <div class="query-stat">
                            <div class="query-stat-label">Top Category</div>
                            <div class="query-stat-value">${report.query_stats.top_category}</div>
                        </div>
                        <div class="query-stat">
                            <div class="query-stat-label">First Query</div>
                            <div class="query-stat-value">${report.query_stats.first_query}</div>
                        </div>
                        <div class="query-stat">
                            <div class="query-stat-label">Last Query</div>
                            <div class="query-stat-value">${report.query_stats.last_query}</div>
                        </div>
                        <div class="query-stat">
                            <div class="query-stat-label">Most Active Hour</div>
                            <div class="query-stat-value">${report.query_stats.most_active_query_hour}</div>
                        </div>
                    </div>
                </div>
                
                <div class="top-questions">
                    <h3>❓ Top 5 Most Frequently Asked Questions</h3>
                    ${report.top_questions.map((q, i) => `
                        <div class="question-item">
                            <div style="font-weight: 600; color: #1565c0;">${i + 1}. ${q.question}</div>
                            <div style="color: #666; font-size: 0.9rem; margin-top: 0.3rem;">Asked ${q.count} times</div>
                        </div>
                    `).join('')}
                </div>
            `;
            document.getElementById('reportData').innerHTML = html;
            
            // Create charts after DOM is updated
            setTimeout(() => {
                createConversationChart(report);
                createQueryChart(report);
            }, 100);
        }

        function createConversationChart(report) {
            const ctx = document.getElementById('conversationChart').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Today', 'Overall'],
                    datasets: [{
                        data: [report.total_conversations_today, report.total_conversations - report.total_conversations_today],
                        backgroundColor: [
                            '#1565c0',
                            '#e6f0ff'
                        ],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                usePointStyle: true
                            }
                        },
                        title: {
                            display: true,
                            text: 'Conversation Distribution',
                            color: '#1565c0',
                            font: {
                                size: 16,
                                weight: 'bold'
                            }
                        }
                    }
                }
            });
        }

        function createQueryChart(report) {
            const ctx = document.getElementById('queryChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Total Queries', 'Today\'s Queries'],
                    datasets: [{
                        label: 'Support Queries',
                        data: [report.query_stats.total_queries, report.query_stats.queries_today],
                        backgroundColor: [
                            'rgba(21, 101, 192, 0.8)',
                            'rgba(21, 101, 192, 0.4)'
                        ],
                        borderColor: [
                            'rgba(21, 101, 192, 1)',
                            'rgba(21, 101, 192, 0.6)'
                        ],
                        borderWidth: 2,
                        borderRadius: 8,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        title: {
                            display: true,
                            text: 'Support Query Activity',
                            color: '#1565c0',
                            font: {
                                size: 16,
                                weight: 'bold'
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(21, 101, 192, 0.1)'
                            },
                            ticks: {
                                color: '#1565c0'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#1565c0'
                            }
                        }
                    }
                }
            });
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('reportModal');
            if (event.target === modal) {
                closeReport();
            }
        }
    </script>
</body>
</html> 