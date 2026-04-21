<?php
// ENHANCED USER DASHBOARD - Salem Dominion Ministries
// Features: Bible Verses, Quiz Games, Pastor Posts, Interactive Elements
require_once 'db_connection.php';

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$conn = getConnection();
$user_id = $_SESSION['user_id'];
$user_info = null;

// Get user information
if ($conn) {
    $user_stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    if ($user_stmt) {
        $user_stmt->bind_param("i", $user_id);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();
        if ($user_result->num_rows > 0) {
            $user_info = $user_result->fetch_assoc();
        }
        $user_stmt->close();
    }
}

// Get Bible verses (daily and random)
$bible_verses = [
    ["John 3:16", "For God so loved the world that he gave his one and only Son, that whoever believes in him shall not perish but have eternal life."],
    ["Jeremiah 29:11", "For I know the plans I have for you, declares the Lord, plans to prosper you and not to harm you, to give you hope and a future."],
    ["Philippians 4:13", "I can do all this through him who gives me strength."],
    ["Isaiah 41:10", "So do not fear, for I am with you; do not be dismayed, for I am your God. I will strengthen you and help you; I will uphold you with my righteous right hand."],
    ["Romans 8:28", "And we know that in all things God works for the good of those who love him, who have been called according to his purpose."],
    ["Proverbs 3:5-6", "Trust in the Lord with all your heart and lean not on your own understanding; in all your ways submit to him, and he will make your paths straight."],
    ["Matthew 11:28", "Come to me, all you who are weary and burdened, and I will give you rest."],
    ["Psalm 23:1", "The Lord is my shepherd, I lack nothing."],
    ["2 Corinthians 5:7", "For we live by faith, not by sight."],
    ["Joshua 1:9", "Have I not commanded you? Be strong and courageous. Do not be afraid; do not be discouraged, for the Lord your God will be with you wherever you go."]
];

// Get daily verse based on date
$day_of_year = date('z') % count($bible_verses);
$daily_verse = $bible_verses[$day_of_year];
$random_verse = $bible_verses[array_rand($bible_verses)];

// Bible Quiz Questions
$quiz_questions = [
    [
        "question" => "Who was the first man created by God?",
        "options" => ["Adam", "Noah", "Abraham", "Moses"],
        "correct" => 0,
        "reference" => "Genesis 2:7"
    ],
    [
        "question" => "How many books are in the Bible?",
        "options" => ["66", "73", "39", "27"],
        "correct" => 0,
        "reference" => "Total books: 39 Old Testament + 27 New Testament"
    ],
    [
        "question" => "Who led the Israelites out of Egypt?",
        "options" => ["David", "Moses", "Joshua", "Abraham"],
        "correct" => 1,
        "reference" => "Exodus 3:10"
    ],
    [
        "question" => "What is the Golden Rule?",
        "options" => [
            "Love your neighbor as yourself",
            "Honor your father and mother",
            "Do not steal",
            "Keep the Sabbath holy"
        ],
        "correct" => 0,
        "reference" => "Matthew 7:12"
    ],
    [
        "question" => "Who wrote most of the New Testament?",
        "options" => ["Peter", "John", "Paul", "James"],
        "correct" => 2,
        "reference" => "Paul wrote 13 books"
    ],
    [
        "question" => "What was Jesus' first miracle?",
        "options" => ["Healing a blind man", "Turning water into wine", "Walking on water", "Raising Lazarus"],
        "correct" => 1,
        "reference" => "John 2:1-11"
    ],
    [
        "question" => "How many disciples did Jesus have?",
        "options" => ["10", "11", "12", "13"],
        "correct" => 2,
        "reference" => "Matthew 10:1-4"
    ],
    [
        "question" => "Where was Jesus born?",
        "options" => ["Jerusalem", "Nazareth", "Bethlehem", "Capernaum"],
        "correct" => 2,
        "reference" => "Luke 2:4-7"
    ]
];

// Get pastor posts (sermons, news, events)
$pastor_posts = [];
if ($conn) {
    try {
        // Get recent sermons
        $sermons_result = $conn->query("SELECT id, title, description, sermon_date, 'sermon' as type, created_at FROM sermons ORDER BY created_at DESC LIMIT 5");
        if ($sermons_result) {
            while ($row = $sermons_result->fetch_assoc()) {
                $pastor_posts[] = $row;
            }
        }
        
        // Get recent news
        $news_result = $conn->query("SELECT id, title, content as description, created_at, 'news' as type FROM news ORDER BY created_at DESC LIMIT 5");
        if ($news_result) {
            while ($row = $news_result->fetch_assoc()) {
                $pastor_posts[] = $row;
            }
        }
        
        // Get upcoming events
        $events_result = $conn->query("SELECT id, title, description, event_date, 'event' as type, created_at FROM events WHERE event_date >= CURDATE() ORDER BY event_date ASC LIMIT 5");
        if ($events_result) {
            while ($row = $events_result->fetch_assoc()) {
                $pastor_posts[] = $row;
            }
        }
        
        // Sort all posts by date
        usort($pastor_posts, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
    } catch (Exception $e) {
        error_log("Error fetching pastor posts: " . $e->getMessage());
    }
}

// Helper function for safe HTML output
function safe_html($string, $default = '') {
    return htmlspecialchars($string ?? $default, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self' https://cdn.jsdelivr.net https://unpkg.com https://fonts.googleapis.com 'unsafe-inline'; style-src 'self' https://cdn.jsdelivr.net https://unpkg.com https://fonts.googleapis.com https://cdnjs.cloudflare.com 'unsafe-inline'; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: https:; connect-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com;">
    <meta name="description" content="Enhanced User Dashboard - Salem Dominion Ministries">
    <title>Enhanced Dashboard - Salem Dominion Ministries</title>
    <link rel="icon" href="public/logo-icon.jpeg">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Playfair+Display:wght@400;700&family=Great+Vibes&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --midnight-blue: #0f172a;
            --heavenly-gold: #fbbf24;
            --ocean-blue: #0ea5e9;
            --snow-white: #ffffff;
            --glass: rgba(255, 255, 255, 0.05);
            --gradient-divine: linear-gradient(135deg, var(--heavenly-gold) 0%, var(--ocean-blue) 100%);
            --gradient-spirit: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --gradient-warmth: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background: linear-gradient(135deg, var(--midnight-blue) 0%, #1e293b 50%, #334155 100%);
            color: var(--snow-white);
            overflow-x: hidden;
            min-height: 100vh;
        }

        /* Enhanced Navigation */
        .navbar {
            background: rgba(15, 23, 42, 0.95) !important;
            backdrop-filter: blur(20px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            padding: 1rem 0;
            transition: all 0.5s ease;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 15px;
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--heavenly-gold) !important;
            text-decoration: none !important;
            transition: all 0.3s ease;
        }

        .navbar-brand:hover {
            transform: scale(1.02);
        }

        .navbar-brand img {
            height: 45px;
            width: auto;
            border-radius: 50%;
            background: var(--snow-white);
            padding: 5px;
            box-shadow: 0 0 20px rgba(251, 191, 36, 0.3);
            transition: all 0.5s ease;
        }

        .navbar-brand:hover img {
            transform: scale(1.1) rotate(5deg);
            box-shadow: 0 0 30px rgba(251, 191, 36, 0.5);
        }

        .navbar-nav .nav-link {
            color: var(--snow-white) !important;
            font-weight: 400;
            margin: 0 8px;
            transition: all 0.3s ease;
            position: relative;
            text-decoration: none !important;
        }

        .navbar-nav .nav-link:hover,
        .navbar-nav .nav-link.active {
            color: var(--heavenly-gold) !important;
            font-weight: 500;
        }

        /* Dashboard Section */
        .dashboard-section {
            padding: 100px 0 80px;
            min-height: 100vh;
        }

        /* Enhanced Cards */
        .feature-card {
            background: var(--glass);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(251, 191, 36, 0.2);
            border-radius: 20px;
            padding: 2rem;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-divine);
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 60px rgba(251, 191, 36, 0.2);
            border-color: rgba(251, 191, 36, 0.3);
        }

        .feature-card.bible-card::before {
            background: var(--gradient-spirit);
        }

        .feature-card.quiz-card::before {
            background: var(--gradient-warmth);
        }

        .feature-card.posts-card::before {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        /* Bible Verse Section */
        .verse-container {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
            border: 1px solid rgba(102, 126, 234, 0.3);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            position: relative;
        }

        .verse-text {
            font-family: 'Playfair Display', serif;
            font-size: 1.3rem;
            line-height: 1.8;
            color: var(--heavenly-gold);
            margin-bottom: 1rem;
            font-style: italic;
            text-align: center;
        }

        .verse-reference {
            text-align: right;
            font-weight: 600;
            color: var(--ocean-blue);
            font-size: 1.1rem;
        }

        .random-verse-btn {
            background: var(--gradient-spirit);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: 500;
            transition: all 0.3s ease;
            cursor: pointer;
            margin-top: 1rem;
        }

        .random-verse-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        /* Quiz Section */
        .quiz-container {
            background: linear-gradient(135deg, rgba(240, 147, 251, 0.1), rgba(245, 87, 108, 0.1));
            border: 1px solid rgba(240, 147, 251, 0.3);
            border-radius: 15px;
            padding: 2rem;
        }

        .quiz-question {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: var(--heavenly-gold);
        }

        .quiz-options {
            display: grid;
            gap: 1rem;
        }

        .quiz-option {
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            padding: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .quiz-option:hover {
            background: rgba(255, 255, 255, 0.2);
            border-color: var(--heavenly-gold);
            transform: translateX(5px);
        }

        .quiz-option.correct {
            background: rgba(34, 197, 94, 0.2);
            border-color: #22c55e;
        }

        .quiz-option.incorrect {
            background: rgba(239, 68, 68, 0.2);
            border-color: #ef4444;
        }

        .quiz-feedback {
            margin-top: 1rem;
            padding: 1rem;
            border-radius: 10px;
            text-align: center;
            font-weight: 500;
        }

        .quiz-feedback.correct {
            background: rgba(34, 197, 94, 0.1);
            color: #22c55e;
        }

        .quiz-feedback.incorrect {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }

        /* Pastor Posts Section */
        .post-item {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .post-item:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
            border-color: rgba(251, 191, 36, 0.3);
        }

        .post-type {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .post-type.sermon {
            background: rgba(251, 191, 36, 0.2);
            color: var(--heavenly-gold);
        }

        .post-type.news {
            background: rgba(14, 165, 233, 0.2);
            color: var(--ocean-blue);
        }

        .post-type.event {
            background: rgba(34, 197, 94, 0.2);
            color: #22c55e;
        }

        .post-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--snow-white);
        }

        .post-description {
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 0.5rem;
            line-height: 1.6;
        }

        .post-meta {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.6);
        }

        /* User Info Section */
        .user-info {
            background: linear-gradient(135deg, rgba(251, 191, 36, 0.1), rgba(14, 165, 233, 0.1));
            border: 1px solid rgba(251, 191, 36, 0.3);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .user-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: var(--gradient-divine);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
            margin-bottom: 1rem;
            box-shadow: 0 0 20px rgba(251, 191, 36, 0.3);
        }

        .welcome-title {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--heavenly-gold);
            margin-bottom: 1rem;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }

        /* Action Buttons */
        .action-btn {
            background: var(--gradient-divine);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 25px;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            margin: 5px;
        }

        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(251, 191, 36, 0.4);
            color: white;
            text-decoration: none;
        }

        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--heavenly-gold);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .section-title i {
            font-size: 1.5rem;
        }

        /* Tab Navigation */
        .tab-nav {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            border-bottom: 2px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 1rem;
        }

        .tab-btn {
            background: none;
            border: none;
            color: rgba(255, 255, 255, 0.7);
            padding: 10px 20px;
            border-radius: 10px 10px 0 0;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .tab-btn.active {
            background: var(--gradient-divine);
            color: white;
        }

        .tab-btn:hover {
            color: var(--heavenly-gold);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        /* Mobile Responsive Design */
        @media (max-width: 768px) {
            .dashboard-section {
                padding: 80px 0 60px;
            }

            .welcome-title {
                font-size: 2rem;
            }

            .feature-card {
                padding: 1.5rem;
            }

            .verse-text {
                font-size: 1.1rem;
            }

            .section-title {
                font-size: 1.5rem;
            }

            .tab-nav {
                flex-wrap: wrap;
                gap: 0.5rem;
            }

            .tab-btn {
                padding: 8px 15px;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 576px) {
            .welcome-title {
                font-size: 1.8rem;
            }

            .feature-card {
                padding: 1.2rem;
            }

            .verse-text {
                font-size: 1rem;
            }

            .user-avatar {
                width: 60px;
                height: 60px;
                font-size: 1.5rem;
            }
        }

        /* Loading Animation */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: var(--heavenly-gold);
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Footer Styles */
        .footer {
            background: linear-gradient(135deg, var(--midnight-blue) 0%, var(--ocean-blue) 100%);
            color: var(--snow-white);
            padding: 40px 0 20px;
            margin-top: 80px;
        }

        .footer a {
            color: var(--snow-white);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .footer a:hover {
            color: var(--heavenly-gold);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="public/logo-icon.jpeg" alt="Salem Dominion Ministries">
                <span>Salem Dominion Ministries</span>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard_enhanced.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="sermons.php">Sermons</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="events.php">Events</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="donate.php">Donate</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link logout-btn" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Enhanced Dashboard Section -->
    <section class="dashboard-section">
        <div class="container">
            <!-- Welcome Section -->
            <div class="row mb-4">
                <div class="col-lg-12">
                    <div class="feature-card" data-aos="fade-up">
                        <h1 class="welcome-title">Welcome to Your Spiritual Dashboard</h1>
                        
                        <?php if ($user_info): ?>
                        <div class="user-info">
                            <div class="row align-items-center">
                                <div class="col-md-2 text-center">
                                    <div class="user-avatar">
                                        <?php echo strtoupper(substr($user_info['first_name'], 0, 1) . substr($user_info['last_name'], 0, 1)); ?>
                                    </div>
                                </div>
                                <div class="col-md-10">
                                    <h3><?php echo safe_html($user_info['first_name'] . ' ' . $user_info['last_name']); ?></h3>
                                    <p class="mb-1"><i class="fas fa-envelope me-2"></i><?php echo safe_html($user_info['email']); ?></p>
                                    <?php if (!empty($user_info['phone'])): ?>
                                    <p class="mb-1"><i class="fas fa-phone me-2"></i><?php echo safe_html($user_info['phone']); ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($user_info['country'])): ?>
                                    <p class="mb-0"><i class="fas fa-globe me-2"></i><?php echo safe_html($user_info['country']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Main Features Grid -->
            <div class="row">
                <!-- Bible Verse Section -->
                <div class="col-lg-4 mb-4">
                    <div class="feature-card bible-card" data-aos="fade-up" data-aos-delay="100">
                        <h2 class="section-title">
                            <i class="fas fa-book-bible"></i>
                            Daily Inspiration
                        </h2>
                        
                        <div class="verse-container">
                            <div class="verse-text" id="dailyVerse">
                                "<?php echo $daily_verse[1]; ?>"
                            </div>
                            <div class="verse-reference">
                                - <?php echo $daily_verse[0]; ?>
                            </div>
                        </div>
                        
                        <div class="verse-container">
                            <h5 style="color: var(--ocean-blue); margin-bottom: 1rem;">Random Verse</h5>
                            <div class="verse-text" id="randomVerse">
                                "<?php echo $random_verse[1]; ?>"
                            </div>
                            <div class="verse-reference" id="randomReference">
                                - <?php echo $random_verse[0]; ?>
                            </div>
                            <button class="random-verse-btn" onclick="getRandomVerse()">
                                <i class="fas fa-sync-alt me-2"></i>Get New Verse
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Bible Quiz Section -->
                <div class="col-lg-4 mb-4">
                    <div class="feature-card quiz-card" data-aos="fade-up" data-aos-delay="200">
                        <h2 class="section-title">
                            <i class="fas fa-brain"></i>
                            Bible Quiz Challenge
                        </h2>
                        
                        <div class="quiz-container">
                            <div id="quizContent">
                                <div class="quiz-question" id="quizQuestion">
                                    Ready to test your Bible knowledge?
                                </div>
                                <div class="quiz-options" id="quizOptions">
                                    <button class="action-btn w-100" onclick="startQuiz()">
                                        <i class="fas fa-play me-2"></i>Start Quiz
                                    </button>
                                </div>
                                <div id="quizFeedback"></div>
                            </div>
                            
                            <div class="text-center mt-3">
                                <small class="text-muted">Score: <span id="quizScore">0</span> / <span id="totalQuestions">0</span></small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="col-lg-4 mb-4">
                    <div class="feature-card" data-aos="fade-up" data-aos-delay="300">
                        <h2 class="section-title">
                            <i class="fas fa-rocket"></i>
                            Quick Actions
                        </h2>
                        
                        <div class="row">
                            <div class="col-6 mb-3">
                                <a href="sermons.php" class="action-btn w-100">
                                    <i class="fas fa-book-open me-2"></i>Sermons
                                </a>
                            </div>
                            <div class="col-6 mb-3">
                                <a href="events.php" class="action-btn w-100">
                                    <i class="fas fa-calendar me-2"></i>Events
                                </a>
                            </div>
                            <div class="col-6 mb-3">
                                <a href="gallery.php" class="action-btn w-100">
                                    <i class="fas fa-images me-2"></i>Gallery
                                </a>
                            </div>
                            <div class="col-6 mb-3">
                                <a href="news.php" class="action-btn w-100">
                                    <i class="fas fa-newspaper me-2"></i>News
                                </a>
                            </div>
                            <div class="col-6 mb-3">
                                <a href="donate.php" class="action-btn w-100">
                                    <i class="fas fa-heart me-2"></i>Donate
                                </a>
                            </div>
                            <div class="col-6 mb-3">
                                <a href="testimonials.php" class="action-btn w-100">
                                    <i class="fas fa-comments me-2"></i>Testimonials
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pastor Posts Section -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="feature-card posts-card" data-aos="fade-up" data-aos-delay="400">
                        <h2 class="section-title">
                            <i class="fas fa-microphone-alt"></i>
                            Pastor's Latest Posts
                        </h2>
                        
                        <!-- Tab Navigation -->
                        <div class="tab-nav">
                            <button class="tab-btn active" onclick="showTab('recent')">
                                <i class="fas fa-clock me-2"></i>Recent Posts
                            </button>
                            <button class="tab-btn" onclick="showTab('sermons')">
                                <i class="fas fa-book-open me-2"></i>Sermons Only
                            </button>
                            <button class="tab-btn" onclick="showTab('news')">
                                <i class="fas fa-newspaper me-2"></i>News Only
                            </button>
                            <button class="tab-btn" onclick="showTab('events')">
                                <i class="fas fa-calendar me-2"></i>Events Only
                            </button>
                            <button class="tab-btn" onclick="showTab('all')">
                                <i class="fas fa-list me-2"></i>View All Posts
                            </button>
                        </div>
                        
                        <!-- Tab Content -->
                        <div id="recentTab" class="tab-content active">
                            <?php if (!empty($pastor_posts)): ?>
                                <?php 
                                $recent_posts = array_slice($pastor_posts, 0, 5);
                                foreach ($recent_posts as $post): 
                                ?>
                                <div class="post-item" onclick="viewPost('<?php echo $post['type']; ?>', <?php echo $post['id']; ?>)">
                                    <span class="post-type <?php echo $post['type']; ?>">
                                        <i class="fas fa-<?php echo $post['type'] === 'sermon' ? 'book-open' : ($post['type'] === 'news' ? 'newspaper' : 'calendar'); ?> me-1"></i>
                                        <?php echo ucfirst($post['type']); ?>
                                    </span>
                                    <h4 class="post-title"><?php echo safe_html($post['title']); ?></h4>
                                    <p class="post-description"><?php echo safe_html(substr($post['description'], 0, 150)); ?>...</p>
                                    <div class="post-meta">
                                        <i class="fas fa-clock me-1"></i>
                                        <?php 
                                        if ($post['type'] === 'event') {
                                            echo date('M j, Y', strtotime($post['event_date']));
                                        } else {
                                            echo date('M j, Y', strtotime($post['created_at']));
                                        }
                                        ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-microphone-alt" style="font-size: 3rem; color: var(--heavenly-gold); opacity: 0.5;"></i>
                                    <p class="mt-3">No posts available at the moment. Check back soon!</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div id="sermonsTab" class="tab-content">
                            <?php 
                            $sermons_only = array_filter($pastor_posts, function($post) { return $post['type'] === 'sermon'; });
                            if (!empty($sermons_only)): ?>
                                <?php foreach (array_slice($sermons_only, 0, 5) as $post): ?>
                                <div class="post-item" onclick="viewPost('sermon', <?php echo $post['id']; ?>)">
                                    <span class="post-type sermon">
                                        <i class="fas fa-book-open me-1"></i>Sermon
                                    </span>
                                    <h4 class="post-title"><?php echo safe_html($post['title']); ?></h4>
                                    <p class="post-description"><?php echo safe_html(substr($post['description'], 0, 150)); ?>...</p>
                                    <div class="post-meta">
                                        <i class="fas fa-clock me-1"></i><?php echo date('M j, Y', strtotime($post['sermon_date'])); ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-book-open" style="font-size: 3rem; color: var(--heavenly-gold); opacity: 0.5;"></i>
                                    <p class="mt-3">No sermons available at the moment.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div id="newsTab" class="tab-content">
                            <?php 
                            $news_only = array_filter($pastor_posts, function($post) { return $post['type'] === 'news'; });
                            if (!empty($news_only)): ?>
                                <?php foreach (array_slice($news_only, 0, 5) as $post): ?>
                                <div class="post-item" onclick="viewPost('news', <?php echo $post['id']; ?>)">
                                    <span class="post-type news">
                                        <i class="fas fa-newspaper me-1"></i>News
                                    </span>
                                    <h4 class="post-title"><?php echo safe_html($post['title']); ?></h4>
                                    <p class="post-description"><?php echo safe_html(substr($post['description'], 0, 150)); ?>...</p>
                                    <div class="post-meta">
                                        <i class="fas fa-clock me-1"></i><?php echo date('M j, Y', strtotime($post['created_at'])); ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-newspaper" style="font-size: 3rem; color: var(--heavenly-gold); opacity: 0.5;"></i>
                                    <p class="mt-3">No news articles available at the moment.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div id="eventsTab" class="tab-content">
                            <?php 
                            $events_only = array_filter($pastor_posts, function($post) { return $post['type'] === 'event'; });
                            if (!empty($events_only)): ?>
                                <?php foreach (array_slice($events_only, 0, 5) as $post): ?>
                                <div class="post-item" onclick="viewPost('event', <?php echo $post['id']; ?>)">
                                    <span class="post-type event">
                                        <i class="fas fa-calendar me-1"></i>Event
                                    </span>
                                    <h4 class="post-title"><?php echo safe_html($post['title']); ?></h4>
                                    <p class="post-description"><?php echo safe_html(substr($post['description'], 0, 150)); ?>...</p>
                                    <div class="post-meta">
                                        <i class="fas fa-clock me-1"></i><?php echo date('M j, Y', strtotime($post['event_date'])); ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-calendar" style="font-size: 3rem; color: var(--heavenly-gold); opacity: 0.5;"></i>
                                    <p class="mt-3">No upcoming events at the moment.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div id="allTab" class="tab-content">
                            <?php if (!empty($pastor_posts)): ?>
                                <?php foreach ($pastor_posts as $post): ?>
                                <div class="post-item" onclick="viewPost('<?php echo $post['type']; ?>', <?php echo $post['id']; ?>)">
                                    <span class="post-type <?php echo $post['type']; ?>">
                                        <i class="fas fa-<?php echo $post['type'] === 'sermon' ? 'book-open' : ($post['type'] === 'news' ? 'newspaper' : 'calendar'); ?> me-1"></i>
                                        <?php echo ucfirst($post['type']); ?>
                                    </span>
                                    <h4 class="post-title"><?php echo safe_html($post['title']); ?></h4>
                                    <p class="post-description"><?php echo safe_html(substr($post['description'], 0, 150)); ?>...</p>
                                    <div class="post-meta">
                                        <i class="fas fa-clock me-1"></i>
                                        <?php 
                                        if ($post['type'] === 'event') {
                                            echo date('M j, Y', strtotime($post['event_date']));
                                        } else {
                                            echo date('M j, Y', strtotime($post['created_at']));
                                        }
                                        ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-microphone-alt" style="font-size: 3rem; color: var(--heavenly-gold); opacity: 0.5;"></i>
                                    <p class="mt-3">No posts available at the moment. Check back soon!</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Features Row -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="feature-card" data-aos="fade-up" data-aos-delay="500">
                        <h3 class="section-title">
                            <i class="fas fa-pray"></i>
                            Prayer Requests
                        </h3>
                        <p>Share your prayer requests with our community and receive support from fellow believers.</p>
                        <button class="action-btn" onclick="window.location.href='prayer.php'">
                            <i class="fas fa-hands-praying me-2"></i>Submit Prayer Request
                        </button>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="feature-card" data-aos="fade-up" data-aos-delay="600">
                        <h3 class="section-title">
                            <i class="fas fa-users"></i>
                            Community
                        </h3>
                        <p>Connect with other church members, join groups, and participate in community activities.</p>
                        <button class="action-btn" onclick="window.location.href='community.php'">
                            <i class="fas fa-user-friends me-2"></i>Join Community
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Professional Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p class="text-white-50 mb-0">&copy; <?= date('Y') ?> Salem Dominion Ministries. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="privacy.php" class="text-white-50 me-3 text-decoration-none">Privacy Policy</a>
                    <a href="terms.php" class="text-white-50 text-decoration-none">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS Animation -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>AOS.init({ duration: 1000, once: true });</script>
    
    <script>
        // Bible verses data
        const bibleVerses = <?php echo json_encode($bible_verses); ?>;
        
        // Quiz questions data
        const quizQuestions = <?php echo json_encode($quiz_questions); ?>;
        let currentQuestionIndex = 0;
        let score = 0;
        let totalQuestionsAnswered = 0;
        
        // Get random verse
        function getRandomVerse() {
            const randomIndex = Math.floor(Math.random() * bibleVerses.length);
            const verse = bibleVerses[randomIndex];
            
            document.getElementById('randomVerse').innerHTML = `"${verse[1]}"`;
            document.getElementById('randomReference').innerHTML = `- ${verse[0]}`;
            
            // Add animation
            const verseContainer = document.querySelector('.verse-container:last-child');
            verseContainer.style.opacity = '0';
            setTimeout(() => {
                verseContainer.style.opacity = '1';
            }, 100);
        }
        
        // Start quiz
        function startQuiz() {
            currentQuestionIndex = 0;
            score = 0;
            totalQuestionsAnswered = 0;
            showQuestion();
        }
        
        // Show question
        function showQuestion() {
            if (currentQuestionIndex >= quizQuestions.length) {
                showQuizResults();
                return;
            }
            
            const question = quizQuestions[currentQuestionIndex];
            const quizContent = document.getElementById('quizContent');
            
            quizContent.innerHTML = `
                <div class="quiz-question">${question.question}</div>
                <div class="quiz-options">
                    ${question.options.map((option, index) => `
                        <div class="quiz-option" onclick="checkAnswer(${index})">
                            ${option}
                        </div>
                    `).join('')}
                </div>
                <div id="quizFeedback"></div>
            `;
        }
        
        // Check answer
        function checkAnswer(selectedIndex) {
            const question = quizQuestions[currentQuestionIndex];
            const options = document.querySelectorAll('.quiz-option');
            const feedback = document.getElementById('quizFeedback');
            
            totalQuestionsAnswered++;
            
            // Disable all options
            options.forEach(option => {
                option.style.pointerEvents = 'none';
            });
            
            // Show correct/incorrect
            if (selectedIndex === question.correct) {
                options[selectedIndex].classList.add('correct');
                score++;
                feedback.innerHTML = `<div class="quiz-feedback correct">Correct! ${question.reference}</div>`;
            } else {
                options[selectedIndex].classList.add('incorrect');
                options[question.correct].classList.add('correct');
                feedback.innerHTML = `<div class="quiz-feedback incorrect">Incorrect. ${question.reference}</div>`;
            }
            
            // Update score
            document.getElementById('quizScore').textContent = score;
            document.getElementById('totalQuestions').textContent = totalQuestionsAnswered;
            
            // Next question after delay
            setTimeout(() => {
                currentQuestionIndex++;
                showQuestion();
            }, 3000);
        }
        
        // Show quiz results
        function showQuizResults() {
            const percentage = Math.round((score / totalQuestionsAnswered) * 100);
            let message = '';
            
            if (percentage >= 80) {
                message = 'Excellent! You really know your Bible!';
            } else if (percentage >= 60) {
                message = 'Good job! Keep studying God\'s Word!';
            } else {
                message = 'Keep learning! The Bible is full of wisdom!';
            }
            
            document.getElementById('quizContent').innerHTML = `
                <div class="text-center">
                    <h4 style="color: var(--heavenly-gold); margin-bottom: 1rem;">Quiz Complete!</h4>
                    <p style="font-size: 1.2rem; margin-bottom: 1rem;">Your Score: ${score}/${totalQuestionsAnswered} (${percentage}%)</p>
                    <p style="margin-bottom: 1.5rem;">${message}</p>
                    <button class="action-btn" onclick="startQuiz()">
                        <i class="fas fa-redo me-2"></i>Try Again
                    </button>
                </div>
            `;
        }
        
        // Tab navigation
        function showTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Remove active class from all buttons
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById(tabName + 'Tab').classList.add('active');
            
            // Add active class to clicked button
            event.target.classList.add('active');
        }
        
        // View post function
        function viewPost(type, id) {
            switch(type) {
                case 'sermon':
                    window.location.href = 'sermon_detail.php?id=' + id;
                    break;
                case 'news':
                    window.location.href = 'news_detail.php?id=' + id;
                    break;
                case 'event':
                    window.location.href = 'event_detail.php?id=' + id;
                    break;
                default:
                    console.log('Unknown post type:', type);
            }
        }
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            // Set initial score display
            document.getElementById('quizScore').textContent = '0';
            document.getElementById('totalQuestions').textContent = '0';
        });
    </script>
</body>
</html>
