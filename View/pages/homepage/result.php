<?php
    session_start();
    require_once __DIR__ . '/../../../Connection/Connection.php'; 
    if (!defined('BASE_URL')) {
        define('BASE_URL', '.');
    }

    if (!isset($_SESSION['user_id'])) {
        header('Location: ../auth/index.php');
        exit;
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fishing Analysis Result</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/View/Assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    <style>
        :root {
            --primary-blue: #2563eb;
            --success-green: #10b981;
            --warning-yellow: #f59e0b;
            --danger-red: #ef4444;
            --dark-bg: #0f172a;
            --card-bg: #1e293b;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            color: white;
        }

        .result-container {
            padding: 20px 15px;
            max-width: 500px;
            margin: 0 auto;
            padding-bottom: 120px; /* Extra padding for mobile navigation */
            min-height: 100vh; /* Ensure full height */
        }

        .header-section {
            text-align: center;
            margin-bottom: 30px;
            padding-top: 20px;
        }

        .back-button {
            position: fixed; /* Changed from absolute to fixed */
            top: 20px;
            left: 20px;
            background: rgba(255,255,255,0.1);
            border: none;
            color: white;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(10px);
            cursor: pointer; /* Add cursor pointer */
            transition: all 0.3s ease; /* Add smooth transition */
            z-index: 1000; /* Ensure it's above other elements */
        }

        .back-button:hover {
            background: rgba(255,255,255,0.2);
            transform: scale(1.05);
        }

        .back-button:active {
            transform: scale(0.95);
        }

        .percentage-circle {
            width: 200px;
            height: 200px;
            margin: 0 auto 20px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .circle-progress {
            transform: rotate(-90deg);
        }

        .circle-bg {
            fill: none;
            stroke: rgba(255,255,255,0.1);
            stroke-width: 8;
        }

        .circle-fill {
            fill: none;
            stroke-width: 8;
            stroke-linecap: round;
            stroke-dasharray: 565.48;
            stroke-dashoffset: 565.48;
            transition: all 2s ease-in-out;
        }

        .percentage-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 3rem;
            font-weight: 700;
            text-align: center;
        }

        .status-badge {
            display: inline-block;
            padding: 8px 20px;
            border-radius: 25px;
            font-weight: 600;
            margin: 10px 0;
        }

        .status-excellent { background: var(--success-green); }
        .status-good { background: var(--primary-blue); }
        .status-fair { background: var(--warning-yellow); color: #000; }
        .status-poor { background: var(--danger-red); }

        .details-card {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 20px;
            margin: 20px 0;
            border: 1px solid rgba(255,255,255,0.1);
        }

        .factor-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .factor-item:last-child {
            border-bottom: none;
        }

        .factor-name {
            font-weight: 500;
            flex: 1;
        }

        .factor-weight {
            color: #94a3b8;
            font-size: 0.9rem;
            margin: 0 15px;
        }

        .factor-score {
            background: rgba(59, 130, 246, 0.2);
            color: #60a5fa;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 600;
            min-width: 50px;
            text-align: center;
        }

        .recommendations-section {
            margin: 20px 0;
        }

        .recommendation-item {
            background: rgba(16, 185, 129, 0.1);
            border-left: 4px solid var(--success-green);
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
        }

        .weather-info {
            display: flex;
            justify-content: space-around;
            background: rgba(59, 130, 246, 0.1);
            padding: 15px;
            border-radius: 10px;
            margin: 20px 0;
        }

        .weather-item {
            text-align: center;
        }

        .weather-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #60a5fa;
        }

        .weather-label {
            font-size: 0.9rem;
            color: #94a3b8;
        }

        .analyze-again-btn {
            background: linear-gradient(135deg, var(--primary-blue) 0%, #1d4ed8 100%);
            border: none;
            padding: 15px 30px;
            border-radius: 50px;
            color: white;
            font-weight: 600;
            width: 100%;
            margin: 20px 0;
            transition: transform 0.2s;
        }

        .analyze-again-btn:hover {
            transform: translateY(-2px);
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-in {
            animation: fadeInUp 0.8s ease-out forwards;
        }

        .loading-skeleton {
            background: linear-gradient(90deg, rgba(255,255,255,0.1) 25%, rgba(255,255,255,0.2) 50%, rgba(255,255,255,0.1) 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
            border-radius: 8px;
            height: 20px;
            margin: 10px 0;
        }

        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }

        /* Mobile-specific improvements */
        @media (max-width: 768px) {
            body {
                padding-bottom: 100px; /* Extra space for mobile navigation */
                overflow-x: hidden; /* Prevent horizontal scroll */
            }
            
            .result-container {
                padding: 20px 10px;
                padding-bottom: 140px; /* More space on mobile */
                margin-bottom: 20px;
            }
            
            .header-section {
                padding-top: 70px; /* Account for back button */
                margin-bottom: 20px;
            }
            
            .percentage-circle {
                width: 180px;
                height: 180px;
            }
            
            .percentage-text {
                font-size: 2.5rem;
            }
            
            .details-card {
                margin: 15px 0;
                padding: 15px;
            }
            
            .factor-item {
                padding: 10px 0;
                font-size: 0.9rem;
            }
            
            .weather-info {
                padding: 12px;
                margin: 15px 0;
            }
            
            .weather-value {
                font-size: 1.3rem;
            }
            
            .recommendation-item {
                padding: 12px;
                margin: 8px 0;
                font-size: 0.9rem;
            }
            
            .analyze-again-btn {
                padding: 12px 25px;
                font-size: 0.95rem;
                margin-bottom: 40px;
            }
        }

        /* Very small screens */
        @media (max-width: 480px) {
            .result-container {
                padding: 15px 8px;
                padding-bottom: 160px;
            }
            
            .back-button {
                width: 40px;
                height: 40px;
                top: 15px;
                left: 15px;
            }
            
            .header-section {
                padding-top: 65px;
            }
            
            .percentage-circle {
                width: 160px;
                height: 160px;
            }
            
            .percentage-text {
                font-size: 2rem;
            }
        }

        /* Ensure scrollable content */
        html, body {
            height: 100%;
            overflow-x: hidden;
            scroll-behavior: smooth;
        }
    </style>
</head>
<body>
    <button class="back-button" onclick="goBack()">
        <i class="fas fa-arrow-left"></i>
    </button>

    <div class="result-container">
        <div class="header-section animate-in">
            <h2>Fishing Analysis Result</h2>
            <p class="text-muted">AI-powered fishing condition assessment</p>
        </div>

        <!-- Loading State -->
        <div id="loading-state">
            <div class="percentage-circle animate-in">
                <div class="loading-skeleton" style="width: 200px; height: 190px; border-radius: 50%;"></div>
            </div>
            <div class="text-center">
                <div class="loading-skeleton" style="width: 150px; height: 30px; margin: 20px auto;"></div>
            </div>
        </div>

        <!-- Result Content -->
        <div id="result-content" style="display: none;">
            <!-- Percentage Circle -->
            <div class="percentage-circle animate-in">
                <svg width="200" height="200" class="circle-progress">
                    <circle cx="100" cy="100" r="90" class="circle-bg"/>
                    <circle cx="100" cy="100" r="90" class="circle-fill" id="progress-circle"/>
                </svg>
                <div class="percentage-text">
                    <span id="percentage-value">0</span>%
                </div>
            </div>

            <!-- Status Badge -->
            <div class="text-center animate-in">
                <div class="status-badge" id="status-badge">Loading...</div>
            </div>

            <!-- Weather Information -->
            <div class="weather-info animate-in">
                <div class="weather-item">
                    <div class="weather-value" id="temperature-display">--°</div>
                    <div class="weather-label">Temperature</div>
                </div>
                <div class="weather-item">
                    <div class="weather-value" id="wind-display">-- mph</div>
                    <div class="weather-label">Wind Speed</div>
                </div>
            </div>

            <!-- Detailed Factors -->
            <div class="details-card animate-in">
                <h5><i class="fas fa-chart-bar"></i> Analysis Breakdown</h5>
                <div id="factors-list">
                    <!-- Factors will be populated by JavaScript -->
                </div>
            </div>

            <!-- Recommendations -->
            <div class="recommendations-section animate-in">
                <div class="details-card">
                    <h5><i class="fas fa-lightbulb"></i> Recommendations</h5>
                    <div id="recommendations-list">
                        <!-- Recommendations will be populated by JavaScript -->
                    </div>
                </div>
            </div>

            <!-- Action Button -->
            <!-- <button class="analyze-again-btn animate-in" onclick="analyzeAgain()">
                <i class="fas fa-camera"></i> 
            </button> -->
        </div>
    </div>

    <!-- Include bottom navigation -->
    <?php @require_once __DIR__ . '/../../../View/Components/bottom-nav.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Simulate loading for better UX
            setTimeout(() => {
                loadResults();
            }, 1500);
        });

        function loadResults() {
            const resultData = localStorage.getItem('fishing_analysis_result');
            
            if (!resultData) {
                // Fallback data for testing
                displayResults({
                    finalPercentage: 75,
                    scores: {
                        color: 80, wave: 70, light: 85, weather: 75,
                        time: 60, vegetation: 90, wind: 80, temperature: 70
                    },
                    weights: {
                        color: 0.15, wave: 0.12, light: 0.10, weather: 0.12,
                        time: 0.15, vegetation: 0.18, wind: 0.10, temperature: 0.08
                    },
                    weather: { temperature: 26, windSpeed: 8 },
                    recommendations: ["Good fishing conditions. You should have success."]
                });
                return;
            }

            const data = JSON.parse(resultData);
            displayResults(data);
        }

        function displayResults(data) {
            document.getElementById('loading-state').style.display = 'none';
            document.getElementById('result-content').style.display = 'block';

            // Animate percentage
            animatePercentage(data.finalPercentage);
            
            // Update status badge
            updateStatusBadge(data.finalPercentage);
            
            // Update weather info
            document.getElementById('temperature-display').textContent = `${data.weather.temperature || '--'}°C`;
            document.getElementById('wind-display').textContent = `${data.weather.windSpeed || '--'} mph`;
            
            // Populate factors
            populateFactors(data.scores, data.weights);
            
            // Populate recommendations
            populateRecommendations(data.recommendations);
        }

        function animatePercentage(targetPercentage) {
            const circle = document.getElementById('progress-circle');
            const percentageText = document.getElementById('percentage-value');
            const circumference = 2 * Math.PI * 90;
            
            // Set color based on percentage
            let color;
            if (targetPercentage >= 80) color = '#10b981'; // Green
            else if (targetPercentage >= 60) color = '#2563eb'; // Blue
            else if (targetPercentage >= 40) color = '#f59e0b'; // Yellow
            else color = '#ef4444'; // Red
            
            circle.style.stroke = color;
            
            // Animate the circle
            let currentPercentage = 0;
            const increment = targetPercentage / 100;
            const timer = setInterval(() => {
                currentPercentage += increment;
                if (currentPercentage >= targetPercentage) {
                    currentPercentage = targetPercentage;
                    clearInterval(timer);
                }
                
                const offset = circumference - (currentPercentage / 100) * circumference;
                circle.style.strokeDashoffset = offset;
                percentageText.textContent = Math.round(currentPercentage);
            }, 20);
        }

        function updateStatusBadge(percentage) {
            const badge = document.getElementById('status-badge');
            if (percentage >= 80) {
                badge.textContent = 'Excellent Conditions';
                badge.className = 'status-badge status-excellent';
            } else if (percentage >= 60) {
                badge.textContent = 'Good Conditions';
                badge.className = 'status-badge status-good';
            } else if (percentage >= 40) {
                badge.textContent = 'Fair Conditions';
                badge.className = 'status-badge status-fair';
            } else {
                badge.textContent = 'Poor Conditions';
                badge.className = 'status-badge status-poor';
            }
        }

        function populateFactors(scores, weights) {
            const factorNames = {
                color: 'Water Clarity',
                wave: 'Wave Conditions',
                light: 'Light Intensity',
                weather: 'Weather Stability',
                time: 'Time of Day',
                vegetation: 'Vegetation/Structure',
                wind: 'Wind Speed',
                temperature: 'Temperature'
            };

            const factorsList = document.getElementById('factors-list');
            factorsList.innerHTML = '';

            Object.keys(scores).forEach(key => {
                const factorDiv = document.createElement('div');
                factorDiv.className = 'factor-item';
                
                const weightPercent = Math.round(weights[key] * 100);
                
                factorDiv.innerHTML = `
                    <span class="factor-name">${factorNames[key]}</span>
                    <span class="factor-weight">${weightPercent}%</span>
                    <span class="factor-score">${scores[key]}</span>
                `;
                
                factorsList.appendChild(factorDiv);
            });
        }

        function populateRecommendations(recommendations) {
            const recommendationsList = document.getElementById('recommendations-list');
            recommendationsList.innerHTML = '';

            recommendations.forEach(rec => {
                const recDiv = document.createElement('div');
                recDiv.className = 'recommendation-item';
                recDiv.textContent = rec;
                recommendationsList.appendChild(recDiv);
            });
        }

        function goBack() {
            // Try multiple back navigation methods for better compatibility
            if (document.referrer && document.referrer !== window.location.href) {
                // Go back to the previous page if it exists and is not the same page
                window.history.back();
            } else {
                // Fallback: redirect to analyze page
                window.location.href = 'analyze.php';
            }
        }

        function analyzeAgain() {
            // Clear the previous analysis data
            localStorage.removeItem('fishing_analysis_result');
            window.location.href = 'analyze.php';
        }

        // Add keyboard navigation for accessibility
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                goBack();
            }
        });

        // Prevent body scroll when modal-like content is shown (if needed)
        function preventBodyScroll() {
            document.body.style.overflow = 'hidden';
        }

        function allowBodyScroll() {
            document.body.style.overflow = 'auto';
        }

        // Smooth scroll to top function (optional)
        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }
    </script>
</body>
</html>
