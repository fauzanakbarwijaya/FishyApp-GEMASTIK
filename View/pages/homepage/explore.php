<?php
    session_start();
    // The BASE_URL constant should be defined in Connection.php.
    // Using require_once ensures it's loaded only once, preventing the warning.
    require_once __DIR__ . '/../../../Connection/Connection.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Explore</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/View/Assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/View/Assets/css/style.css">
    <link rel="shortcut icon" href="<?= BASE_URL ?>/View/Assets/icons/logo-background.png" type="image/x-icon">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        /* General body and map styling */
        body {
            margin: 0;
            padding: 0;
            overflow: hidden; /* Prevent scrollbars */
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }

        #map {
            height: 100vh;
            width: 100vw;
            padding-bottom: 60px; /* Space for bottom nav */
        }

        /* Sidebar Styling */
        .sidebar {
            height: 100%;
            width: 280px;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #f8f9fa;
            z-index: 1050; /* Higher than map */
            transform: translateX(-100%);
            transition: transform 0.3s ease-in-out;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
        }

        .sidebar.open {
            transform: translateX(0);
        }
        
        .sidebar-content {
            padding: 20px;
            overflow-y: auto;
        }

        .sidebar-header {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #dee2e6;
        }
        
        #weather-info .weather-card, #fishing-outlook {
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            color: white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            margin-bottom: 20px;
        }
        
        #weather-info .weather-card {
             background: linear-gradient(135deg, #6dd5ed, #2193b0);
        }

        #weather-info .temperature {
            font-size: 3rem;
            font-weight: bold;
        }

        #weather-info .condition {
            font-size: 1.2rem;
            margin-top: 5px;
            text-transform: capitalize;
        }
        
        .weather-details {
            text-align: left;
            margin-top: 15px;
            font-size: 0.9rem;
        }
        
        .post-storm-bonus {
            color: #caffbf;
            font-weight: bold;
        }

        #fishing-outlook {
            background: linear-gradient(135deg, #43e97b, #38f9d7);
        }
        
        #fishing-outlook .recommendation-score {
            font-size: 3rem;
            font-weight: bold;
        }
        #fishing-outlook .recommendation-text {
            font-size: 1.2rem;
            margin-top: 5px;
            font-weight: 500;
        }
        
        .fishing-factors {
            text-align: left;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid rgba(255,255,255,0.3);
            font-size: 0.9rem;
        }
        .fishing-factors ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .fishing-factors li {
            margin-bottom: 5px;
        }
        .factor-good::before { content: '✅ '; }
        .factor-bad::before { content: '❌ '; }
        .factor-neutral::before { content: '⚪ '; }


        /* General styling for map overlay buttons */
        .map-overlay-button {
            position: absolute;
            z-index: 1000; /* On top of the map */
            background-color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
            border: 1px solid #ddd;
            transition: all 0.2s ease;
        }
        .map-overlay-button:hover {
            transform: scale(1.1);
            background-color: #f7f7f7;
        }

        /* Specific button styles */
        #sidebar-toggle-btn {
            top: 20px;
            left: 20px;
            width: 50px;
            height: 50px;
        }
        
        #reset-location-btn {
            bottom: 95px; /* Position it above your bottom nav bar */
            right: 20px;
            width: 60px; /* Made bigger */
            height: 60px; /* Made bigger */
        }
        
        .map-overlay-button img,
        .map-overlay-button svg {
            width: 28px;
            height: 28px;
            transition: transform 0.6s cubic-bezier(.68,-0.55,.27,1.55);
        }

        #sidebar-toggle-btn.spin svg,
        #reset-location-btn.spin img,
        #reset-location-btn.spin svg {
            transform: rotate(360deg);
        }

        /* Styling for the on-map fishing spot markers */
        .fishing-spot-marker {
            border-radius: 50%;
            color: white;
            font-weight: bold;
            font-size: 16px;
            text-align: center;
            line-height: 1; /* Adjust for vertical centering */
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.5);
            border: 2px solid white;
            transition: all 0.3s ease;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.7);
            position: absolute; /* Add position absolute */
            top: 50%; /* Position at middle */
            left: 50%; /* Position at middle */
            transform: translate(-50%, -50%); /* Center the element */
            z-index: 900; /* Make sure it's above other map elements */
        }
        .spot-good {
            background-color: rgba(76, 175, 80, 0.8); /* Green with 80% transparency - increased from 70% */
        }
        .spot-bad {
            background-color: rgba(244, 67, 54, 0.8); /* Red with 80% transparency - increased from 70% */
        }
        
        @keyframes pulse {
            0% { transform: scale(1) translate(-50%, -50%); }
            50% { transform: scale(1.1) translate(-45%, -45%); }
            100% { transform: scale(1) translate(-50%, -50%); }
        }

        .pulsing-marker {
            animation-name: pulse;
            animation-iteration-count: infinite;
        }

    </style>
</head>
<body>
    <?php
    if (!isset($_SESSION['user_id'])) {
        echo '<script>
            Swal.fire({
                icon: "warning",
                title: "Not Logged In",
                text: "Silakan login untuk mengakses fitur ini.",
                showConfirmButton: true
            }).then(() => {
                window.location.href = "../auth/index.php";
            });
        </script>';
        exit;
    }
    ?>

    <!-- Sidebar -->
    <div id="sidebar" class="sidebar">
        <div class="sidebar-content">
            <div class="sidebar-header">Area Info</div>
            <div id="weather-info"><p>Getting weather data...</p></div>
            <div id="fishing-outlook"><p>Analyzing conditions...</p></div>
        </div>
    </div>

    <!-- Map Container -->
    <div id="map"></div>

    <!-- Map Buttons -->
    <button id="sidebar-toggle-btn" class="map-overlay-button" aria-label="Toggle sidebar">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 6H20M4 12H20M4 18H20" stroke="#333" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </button>
    <button id="reset-location-btn" class="map-overlay-button" aria-label="Reset location">
         <img src="<?= BASE_URL ?>/View/Assets/icons/resetlocation.png" alt="Reset Location">
    </button>

    <!-- Include the bottom navigation bar -->
    <?php require_once __DIR__ . '/../../../View/Components/bottom-nav.php'; ?>

    <script src="<?= BASE_URL ?>/View/Assets/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', async function() {
        // --- Global Variables ---
        let userLat, userLon;
        let fishingSpotsLayer = L.layerGroup();
        const defaultLat = -6.183064;
        const defaultLon = 106.8403371;

        // --- Initialize the Map ---
        const map = L.map('map', { zoomControl: false }).setView([defaultLat, defaultLon], 13);
        L.control.zoom({ position: 'topright' }).addTo(map);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);
        fishingSpotsLayer.addTo(map);

        // --- UI Elements ---
        const sidebar = document.getElementById('sidebar');
        const sidebarToggleBtn = document.getElementById('sidebar-toggle-btn');
        const resetLocationBtn = document.getElementById('reset-location-btn');
        const weatherInfoDiv = document.getElementById('weather-info');
        const fishingOutlookDiv = document.getElementById('fishing-outlook');
        
        // --- Event Listeners ---
        sidebarToggleBtn.addEventListener('click', (e) => {
            sidebarToggleBtn.classList.add('spin');
            setTimeout(() => sidebarToggleBtn.classList.remove('spin'), 600);
            e.stopPropagation();
            sidebar.classList.toggle('open');
        });
        
        resetLocationBtn.addEventListener('click', () => {
            resetLocationBtn.classList.add('spin');
            setTimeout(() => resetLocationBtn.classList.remove('spin'), 600);
            if (userLat && userLon) {
                map.setView([userLat, userLon], 15);
            } else {
                Swal.fire({ icon: 'info', title: 'Location not available', text: 'Please allow location access.' });
            }
        });

        map.on('click', () => sidebar.classList.remove('open'));

        // --- Geolocation & Weather ---
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    userLat = position.coords.latitude;
                    userLon = position.coords.longitude;
                    map.setView([userLat, userLon], 11);
                    L.marker([userLat, userLon]).addTo(map).bindPopup('You are here!').openPopup();
                    L.circle([userLat, userLon], { color: 'blue', fillColor: '#3085d6', fillOpacity: 0.2, radius: position.coords.accuracy }).addTo(map);
                    fetchWeatherAndFishingData(userLat, userLon);
                },
                function(error) {
                    L.marker([defaultLat, defaultLon]).addTo(map).bindPopup('Default Location (Jakarta)').openPopup();
                    weatherInfoDiv.innerHTML = '<p>Could not get weather. Location access denied.</p>';
                    // Still load fishing spots even without user location
                    runFishingSpotPrediction();
                }
            );
        } else {
            L.marker([defaultLat, defaultLon]).addTo(map).bindPopup('Default Location (Jakarta)').openPopup();
            weatherInfoDiv.innerHTML = '<p>Geolocation is not supported by your browser.</p>';
            // Still load fishing spots even without user location
            runFishingSpotPrediction();
        }

        // --- Main Data Fetching and Processing ---
        async function fetchWeatherAndFishingData(lat, lon) {
            try {
                const weatherData = await fetchWeatherData(lat, lon);
                updateSidebar(weatherData);
                runFishingSpotPrediction();
            } catch (error) {
                console.error("Error fetching data:", error);
                weatherInfoDiv.innerHTML = '<p>Could not retrieve weather information.</p>';
                // Still try to load fishing spots with default weather assumptions
                runFishingSpotPrediction();
            }
        }

        // Fetch weather data for specific coordinates
        async function fetchWeatherData(lat, lon) {
            const apiUrl = `https://api.open-meteo.com/v1/forecast?latitude=${lat.toFixed(4)}&longitude=${lon.toFixed(4)}&current=temperature_2m,weather_code,wind_speed_10m,precipitation_probability,cloud_cover,surface_pressure&hourly=surface_pressure,precipitation&past_days=1&timezone=auto`;
            
            const response = await fetch(apiUrl);
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            const data = await response.json();
            const currentWeather = data.current;
            
            currentWeather.pressure_trend = analyzePressureTrend(data.hourly.surface_pressure);
            currentWeather.moon_phase = getMoonPhase();
            currentWeather.post_storm = checkForPostStorm(data.hourly.precipitation);
            
            return currentWeather;
        }

        // --- Spot Prediction and Visualization ---
        async function runFishingSpotPrediction() {
            // Detailed spot information with specific attributes for each location
            const spots = [
                { 
                    name: 'Danau Sunter', 
                    lat: -6.1474, 
                    lon: 106.8701,
                    fishTypes: ['Nila', 'Mas', 'Lele', 'Patin'],
                    bestTimeStart: 15, // 3 PM
                    bestTimeEnd: 19,   // 7 PM
                    waterType: 'freshwater',
                    popularity: 0.8,   // 0-1 scale
                    windSensitivity: 0.6,  // How much wind affects fishing here
                    rainBenefit: 0.7,  // How much rain improves fishing
                    temperatureFactor: 0.5,  // How much temperature affects fishing
                    baseScore: 75 // Base score from historical data
                },
                { 
                    name: 'Waduk Cincin', 
                    lat: -6.1270, 
                    lon: 106.8677,
                    fishTypes: ['Nila', 'Mas', 'Patin', 'Gurame'],
                    bestTimeStart: 6, // 6 AM
                    bestTimeEnd: 10,  // 10 AM
                    waterType: 'freshwater',
                    popularity: 0.7,
                    windSensitivity: 0.4,
                    rainBenefit: 0.8,
                    temperatureFactor: 0.6,
                    baseScore: 70
                },
                { 
                    name: 'Pemancingan Telaga Mina', 
                    lat: -6.3040, 
                    lon: 106.9042,
                    fishTypes: ['Nila', 'Mas', 'Patin', 'Bawal'],
                    bestTimeStart: 8, // 8 AM
                    bestTimeEnd: 17,  // 5 PM
                    waterType: 'managed',
                    popularity: 0.9,
                    windSensitivity: 0.3,
                    rainBenefit: 0.5,
                    temperatureFactor: 0.4,
                    baseScore: 85 // Commercial spots often have higher success rates
                },
                { 
                    name: 'Setu Babakan', 
                    lat: -6.3414, 
                    lon: 106.8195,
                    fishTypes: ['Nila', 'Mas', 'Mujair', 'Lele'],
                    bestTimeStart: 6, // 6 AM
                    bestTimeEnd: 18,  // 6 PM
                    waterType: 'freshwater',
                    popularity: 0.8,
                    windSensitivity: 0.5,
                    rainBenefit: 0.6,
                    temperatureFactor: 0.7,
                    baseScore: 65
                },
                { 
                    name: 'Taman Waduk Pluit', 
                    lat: -6.1152, 
                    lon: 106.7826,
                    fishTypes: ['Nila', 'Mas', 'Mujair', 'Bawal'],
                    bestTimeStart: 16, // 4 PM
                    bestTimeEnd: 19,   // 7 PM
                    waterType: 'freshwater',
                    popularity: 0.6,
                    windSensitivity: 0.7,
                    rainBenefit: 0.5,
                    temperatureFactor: 0.5,
                    baseScore: 60
                },
                { 
                    name: 'Danau UI', 
                    lat: -6.3648, 
                    lon: 106.8271,
                    fishTypes: ['Nila', 'Mas', 'Lele', 'Gabus'],
                    bestTimeStart: 6,  // 6 AM
                    bestTimeEnd: 11,   // 11 AM
                    waterType: 'freshwater',
                    popularity: 0.7,
                    windSensitivity: 0.4,
                    rainBenefit: 0.7,
                    temperatureFactor: 0.5,
                    baseScore: 65
                },
                { 
                    name: 'Pemancingan Galatama PIK', 
                    lat: -6.1120, 
                    lon: 106.7478,
                    fishTypes: ['Nila', 'Mas', 'Patin', 'Bawal'],
                    bestTimeStart: 9,  // 9 AM
                    bestTimeEnd: 21,   // 9 PM
                    waterType: 'managed',
                    popularity: 0.9,
                    windSensitivity: 0.2,
                    rainBenefit: 0.4,
                    temperatureFactor: 0.3,
                    baseScore: 90 // Premium commercial spot
                },
                { 
                    name: 'Situ Gintung', 
                    lat: -6.3066, 
                    lon: 106.7675,
                    fishTypes: ['Nila', 'Mas', 'Mujair', 'Gurame'],
                    bestTimeStart: 7,  // 7 AM
                    bestTimeEnd: 17,   // 5 PM
                    waterType: 'freshwater',
                    popularity: 0.7,
                    windSensitivity: 0.6,
                    rainBenefit: 0.7,
                    temperatureFactor: 0.6,
                    baseScore: 70
                },
                { 
                    name: 'Waduk Ria Rio', 
                    lat: -6.1737, 
                    lon: 106.8846,
                    fishTypes: ['Nila', 'Mas', 'Lele', 'Gurame'],
                    bestTimeStart: 6,  // 6 AM
                    bestTimeEnd: 10,   // 10 AM
                    waterType: 'freshwater',
                    popularity: 0.6,
                    windSensitivity: 0.5,
                    rainBenefit: 0.6,
                    temperatureFactor: 0.5,
                    baseScore: 60
                },
                { 
                    name: 'Pemancingan Monster Fish', 
                    lat: -6.3362, 
                    lon: 106.7384,
                    fishTypes: ['Nila', 'Mas', 'Patin', 'Gurame', 'Bawal'],
                    bestTimeStart: 8,  // 8 AM
                    bestTimeEnd: 20,   // 8 PM
                    waterType: 'managed',
                    popularity: 0.8,
                    windSensitivity: 0.3,
                    rainBenefit: 0.5,
                    temperatureFactor: 0.4,
                    baseScore: 85
                }
            ];

            fishingSpotsLayer.clearLayers();
            const currentHour = new Date().getHours();
            
            // Process all spots with their own weather data
            for (const spot of spots) {
                // Create temporary placeholder marker while fetching weather
                const tempIcon = L.divIcon({
                    html: `<div class="fishing-spot-marker" style="width:50px; height:50px; background-color:rgba(128,128,128,0.8);">...</div>`,
                    className: '',
                    iconSize: [50, 50],
                    iconAnchor: [25, 25]
                });
                
                const tempMarker = L.marker([spot.lat, spot.lon], { icon: tempIcon })
                    .bindPopup(`<b>${spot.name}</b><br>Loading fishing data...`);
                
                fishingSpotsLayer.addLayer(tempMarker);
                
                // Fetch weather specifically for this spot
                try {
                    const spotWeather = await fetchWeatherData(spot.lat, spot.lon);
                    
                    // Calculate score using location-specific weather
                    let score = calculateSpotScore(spot, spotWeather, currentHour);
                    
                    // Update marker with real score
                    const size = 50;
                    const colorClass = score >= 50 ? 'spot-good' : 'spot-bad';
                    
                    const icon = L.divIcon({
                        html: `<div class="fishing-spot-marker ${colorClass}" style="width:${size}px; height:${size}px;">${score.toFixed(0)}%</div>`,
                        className: '',
                        iconSize: [size, size],
                        iconAnchor: [size/2, size/2]
                    });

                    fishingSpotsLayer.removeLayer(tempMarker);
                    
                    const spotMarker = L.marker([spot.lat, spot.lon], { icon: icon })
                        .bindPopup(`
                            <b>${spot.name}</b><br>
                            Fishing Score: ${score.toFixed(0)}%<br>
                            Weather: ${Math.round(spotWeather.temperature_2m)}°C, ${interpretWeatherCode(spotWeather.weather_code)}<br>
                            Best for: ${spot.fishTypes.join(', ')}
                        `);
                    
                    fishingSpotsLayer.addLayer(spotMarker);
                    
                } catch (error) {
                    console.error(`Error fetching weather for ${spot.name}:`, error);
                    // Keep the temporary marker but update it to show error
                    tempMarker.setPopupContent(`<b>${spot.name}</b><br>Could not fetch weather data<br>Best for: ${spot.fishTypes.join(', ')}`);
                }
            }
        }

        // Calculate a specific score for each fishing spot based on its characteristics and weather conditions
        function calculateSpotScore(spot, weather, currentHour) {
            let score = spot.baseScore;
            
            // Time of day factor - is it during this spot's best fishing hours?
            const isOptimalTime = currentHour >= spot.bestTimeStart && currentHour <= spot.bestTimeEnd;
            score += isOptimalTime ? 15 : -10;
            
            // Weather factors adjusted by spot sensitivity
            const isRaining = [51, 53, 55, 61, 63, 65, 80, 81, 82].includes(weather.weather_code);
            const isWindy = weather.wind_speed_10m > 15;
            
            // Wind impact
            if (isWindy) {
                score -= 15 * spot.windSensitivity;
            }
            
            // Rain benefit depends on the spot's characteristics
            if (isRaining && !isWindy) {
                score += 10 * spot.rainBenefit;
            } else if (isRaining && isWindy) {
                score -= 10; // Rain with wind is generally bad everywhere
            }
            
            // Temperature impact based on spot's temperature sensitivity
            const idealTemp = weather.temperature_2m > 22 && weather.temperature_2m < 31;
            const extremeTemp = weather.temperature_2m > 33 || weather.temperature_2m < 20;
            if (idealTemp) {
                score += 5 * spot.temperatureFactor;
            } else if (extremeTemp) {
                score -= 10 * spot.temperatureFactor;
            }
            
            // Moon phase is especially important for natural water bodies
            if (spot.waterType !== 'managed' && 
                (weather.moon_phase.phaseName === 'Full Moon' || weather.moon_phase.phaseName === 'New Moon')) {
                score += 10;
            }
            
            // Post-storm effect is stronger in natural water bodies
            if (weather.post_storm && spot.waterType !== 'managed') {
                score += 15;
            }
            
            // Pressure trends affect natural waters more
            if (spot.waterType !== 'managed') {
                if (weather.pressure_trend === 'Falling') {
                    score += 10;
                } else if (weather.pressure_trend === 'Rising') {
                    score -= 5;
                }
            }
            
            // Get a baseline recommendation for comparison
            const baseRecommendation = getFishingRecommendation(weather);
            
            // Blend spot-specific score with global recommendation (70/30 split)
            score = (score * 0.7) + (baseRecommendation.percentage * 0.3);
            
            // Ensure score stays within bounds
            return Math.max(0, Math.min(100, Math.round(score)));
        }

        // --- UI Update and Helper Functions ---
        function updateSidebar(weather) {
            const condition = interpretWeatherCode(weather.weather_code);
            const postStormHtml = weather.post_storm ? `<br><span class="post-storm-bonus">Post-Storm Feeding Active!</span>` : '';

            weatherInfoDiv.innerHTML = `
                <div class="weather-card">
                    <div class="temperature">${Math.round(weather.temperature_2m)}°C</div>
                    <div class="condition">${condition}</div>
                    <div class="weather-details">
                        Wind: ${weather.wind_speed_10m.toFixed(1)} km/h <br>
                        Pressure: ${weather.surface_pressure.toFixed(0)} hPa (${weather.pressure_trend}) <br>
                        Moon Phase: ${weather.moon_phase.phaseName}
                        ${postStormHtml}
                    </div>
                </div>
            `;

            const fishingRec = getFishingRecommendation(weather);
            let factorsHtml = '<ul>';
            fishingRec.factors.forEach(factor => {
                factorsHtml += `<li class="${factor.type}">${factor.text}</li>`;
            });
            factorsHtml += '</ul>';

            fishingOutlookDiv.innerHTML = `
                <div class="recommendation-score">${fishingRec.percentage}%</div>
                <div class="recommendation-text">${fishingRec.summary}</div>
                <div class="fishing-factors">${factorsHtml}</div>
            `;
        }
        
        function getFishingRecommendation(weather) {
            let score = 60;
            let factors = [];
            const currentHour = new Date().getHours();

            if (weather.post_storm) { score += 30; factors.push({ type: 'factor-good', text: 'Post-storm feeding frenzy!' }); }
            if (weather.moon_phase.phaseName === 'Full Moon' || weather.moon_phase.phaseName === 'New Moon') { score += 20; factors.push({ type: 'factor-good', text: `Major feeding time (${weather.moon_phase.phaseName})` }); } 
            else if (weather.moon_phase.phaseName === 'First Quarter' || weather.moon_phase.phaseName === 'Last Quarter') { score += 10; factors.push({ type: 'factor-good', text: `Minor feeding time (${weather.moon_phase.phaseName})` }); }
            if (currentHour >= 5 && currentHour <= 9) { score += 20; factors.push({ type: 'factor-good', text: 'Dawn feeding time' }); } 
            else if (currentHour >= 17 && currentHour <= 20) { score += 20; factors.push({ type: 'factor-good', text: 'Dusk feeding time' }); }
            if (weather.pressure_trend === 'Falling') { score += 20; factors.push({ type: 'factor-good', text: 'Pressure is falling' }); }
            else if (weather.pressure_trend === 'Rising') { score -= 10; factors.push({ type: 'factor-bad', text: 'Pressure is rising' }); }
            else { score += 5; factors.push({ type: 'factor-good', text: 'Pressure is stable' }); }
            const isRaining = [51, 53, 55, 61, 63, 65, 80, 81, 82].includes(weather.weather_code);
            const isWindy = weather.wind_speed_10m > 15;
            const isSunny = weather.cloud_cover <= 40;
            if ([95, 96, 99].includes(weather.weather_code)) { score = 5; factors.push({ type: 'factor-bad', text: 'Thunderstorm nearby! Unsafe.' }); }
            else if (isRaining && isWindy) { score -= 25; factors.push({ type: 'factor-bad', text: 'Windy and rainy conditions' }); }
            else if (isRaining) { score += 15; factors.push({ type: 'factor-good', text: 'Light rain (good cover)' }); }
            else if (isSunny && !isWindy) { score -= 15; factors.push({ type: 'factor-bad', text: 'Bright sun, calm water' }); }
            else if (isWindy) { score -= 15; factors.push({ type: 'factor-bad', text: 'Windy conditions' }); }
            else { score += 5; factors.push({ type: 'factor-good', text: 'Calm weather' }); }
            if (weather.temperature_2m > 22 && weather.temperature_2m < 31) { score += 5; factors.push({ type: 'factor-good', text: 'Ideal temperature' }); } 
            else if (weather.temperature_2m > 33 || weather.temperature_2m < 20) { score -= 10; factors.push({ type: 'factor-bad', text: 'Extreme temperature' }); }
            
            score = Math.max(0, Math.min(100, score));

            let summary = "Not Recommended";
            if (score >= 85) summary = "Excellent time to fish!";
            else if (score >= 70) summary = "Good fishing conditions.";
            else if (score >= 50) summary = "Conditions are fair.";
            else if (score >= 30) summary = "Poor conditions.";

            return { percentage: Math.round(score), summary: summary, factors: factors };
        }

        function analyzePressureTrend(hourlyPressure) {
            if (hourlyPressure.length < 4) return 'Stable';
            const currentPressure = hourlyPressure[hourlyPressure.length - 1];
            const pastPressure = hourlyPressure[hourlyPressure.length - 4];
            const difference = currentPressure - pastPressure;
            if (difference < -0.5) return 'Falling';
            if (difference > 0.5) return 'Rising';
            return 'Stable';
        }

        function checkForPostStorm(hourlyPrecipitation) {
            if (!hourlyPrecipitation) return false;
            const past24h = hourlyPrecipitation.slice(-24);
            const totalPrecip = past24h.reduce((acc, val) => acc + val, 0);
            return totalPrecip > 5;
        }
        
        function getMoonPhase() {
            const phases = ['New Moon', 'Waxing Crescent', 'First Quarter', 'Waxing Gibbous', 'Full Moon', 'Waning Gibbous', 'Last Quarter', 'Waning Crescent'];
            const now = new Date();
            const cycleLength = 29.53;
            const knownNewMoon = new Date('2000-01-06T18:14:00Z');
            const daysSince = (now - knownNewMoon) / (1000 * 60 * 60 * 24);
            const currentCyclePos = (daysSince / cycleLength) % 1;
            const phaseIndex = Math.floor(currentCyclePos * 8 + 0.5) & 7;
            return { phaseName: phases[phaseIndex], index: phaseIndex };
        }

        function interpretWeatherCode(code) {
            const wmoCodes = { 0: "Clear sky", 1: "Mainly clear", 2: "Partly cloudy", 3: "Overcast", 45: "Fog", 48: "Rime fog", 51: "Light drizzle", 53: "Drizzle", 55: "Dense drizzle", 61: "Slight rain", 63: "Rain", 65: "Heavy rain", 71: "Slight snow", 73: "Snow", 75: "Heavy snow", 80: "Rain showers", 81: "Moderate showers", 82: "Violent showers", 95: "Thunderstorm" };
            return wmoCodes[code] || "Unknown";
        }
    });
    </script>

</body>
</html>
