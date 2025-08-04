<?php
    session_start();
    // Assuming Connection.php defines BASE_URL. This is crucial for your environment.
    require_once __DIR__ . '/../../../Connection/Connection.php'; 
    // Defining a fallback for demo purposes if Connection.php is not found.
    if (!defined('BASE_URL')) {
        define('BASE_URL', '.');
    }

    // If the user is not logged in, redirect them immediately.
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../auth/index.php');
        exit;
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Analyze</title>
    
    <!-- Bootstrap CSS (Optional, as custom styles handle most of the layout) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    
    <!-- Google Fonts: Montserrat -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- Font Awesome for Icons (kept for fallback, but can be removed) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    <!-- Custom Styles -->
    <style>
        /* Basic setup */
        :root {
            --primary-blue: #2563eb; /* A modern blue for highlights */
            --nav-bg: #0d172a; /* Dark blue from screenshot */
            --error-red: #e74c3c;
            --info-green: #2ecc71;
        }

        body, html {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden;
            font-family: 'Montserrat', sans-serif;
            background-color: #000;
        }

        /* Camera container */
        .camera-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
            background-color: #000;
        }

        #camera-feed, #image-preview {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        #image-preview {
            display: none; /* Hidden by default */
        }

        /* Overlay for UI elements - Using CSS Grid for robust layout */
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 2;
            display: grid; /* Use Grid */
            grid-template-rows: auto 1fr auto; /* Header, flexible space, footer */
            pointer-events: none;
            background: linear-gradient(to bottom, rgba(0,0,0,0.5) 0%, rgba(0,0,0,0) 25%, rgba(0,0,0,0) 75%, rgba(0,0,0,0.7) 100%);
        }
        
        .overlay > * {
            pointer-events: auto;
        }

        /* Top info bar */
        .top-info {
            grid-row: 1 / 2; /* Place in the first row (header) */
            display: flex;
            justify-content: space-between;
            align-items: center; /* Vertically center items */
            padding: 1.5rem;
            color: white;
            text-shadow: 0 2px 4px rgba(0,0,0,0.6);
            font-size: 1.1rem;
            font-weight: 500;
        }
        
        #reset-button {
            background: rgba(0,0,0,0.4);
            border: 1px solid rgba(255,255,255,0.5);
            color: white;
            border-radius: 50%;
            width: 44px;
            height: 44px;
            cursor: pointer;
            display: none; /* Initially hidden */
            align-items: center;
            justify-content: center;
        }
        
        #reset-button img {
            width: 24px;
            height: 24px;
        }

        /* This empty div will occupy the middle space, pushing the footer down */
        .grid-spacer {
            grid-row: 2 / 3;
        }

        /* Container for all bottom elements */
        .footer-container {
            grid-row: 3 / 4; /* Place in the third row (footer) */
            width: 100%;
            z-index: 10;
            padding-bottom: 80px; /* <-- Increase this value to move buttons higher */
        }

        /* Bottom controls for camera actions - LAYOUT FIX */
        .bottom-controls {
            padding: 1rem 1rem 0.1rem 1rem; /* <-- Increase bottom padding for more space */
            display: grid; /* Using Grid for perfect centering */
            grid-template-columns: 1fr auto 1fr; /* 3-column layout */
            align-items: center;
        }

        /* Assigning grid columns to each control */
        #gallery-button-wrapper {
            grid-column: 1 / 2;
            justify-self: center; /* Center within its column */
        }

        .shutter-button-container {
            grid-column: 2 / 3;
            justify-self: center; /* Center within its column */
        }

        #flashlight-button {
            grid-column: 3 / 4;
            justify-self: center; /* Center within its column */
        }

        .control-button {
            background-color: rgba(255, 255, 255, 1);
            border: none;
            border-radius: 50%;
            width: 58px;
            height: 58px;
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            cursor: pointer;
            color: #1c2b46;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        /* Style for image icons within buttons */
        .control-button img {
            width: 28px;
            height: 28px;
        }

        .control-button:active {
            transform: scale(0.92);
            box-shadow: 0 2px 8px rgba(0,0,0,0.4);
        }

        #shutter-button {
            width: 80px;
            height: 80px;
            background-color: white;
            border-radius: 50%;
            box-shadow: 0 0 0 5px var(--primary-blue), 0 5px 20px rgba(0,0,0,0.4);
        }
        
        #shutter-button:active {
            box-shadow: 0 0 0 5px var(--primary-blue), 0 2px 10px rgba(0,0,0,0.5);
        }

        .shutter-button-container label {
            margin-top: 8px;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 1rem;
            color: #2563eb; /* Blue color */
            text-shadow: none;
            display: block;
            text-align: center; /* Center the text under the button */
            letter-spacing: 1px;
        }

        /* Notification System Styles */
        #notification-container {
            position: fixed;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 90%;
            max-width: 500px;
        }

        .notification {
            background-color: #333;
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            margin-top: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            font-size: 0.9rem;
            opacity: 0;
            transform: translateY(-20px);
            animation: fadeIn 0.5s forwards;
        }
        
        .notification.error {
            background-color: var(--error-red);
        }
        .notification.success {
            background-color: var(--info-green);
        }

        @keyframes fadeIn {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Permission Modal */
        #permission-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 2000;
            background-color: rgba(0,0,0,0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            text-align: center;
            padding: 1rem;
            flex-direction: column;
        }
        
        #permission-modal h2 {
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        #permission-modal p {
            max-width: 400px;
            margin-bottom: 2rem;
            line-height: 1.6;
            color: #ccc;
        }
        
        #permission-button {
            background-color: var(--primary-blue);
            color: white;
            border: none;
            padding: 15px 30px;
            font-size: 1rem;
            font-weight: 700;
            border-radius: 50px;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        #permission-button:hover {
            background-color: #1d4ed8;
        }

        .control-button[disabled] {
            opacity: 0.5;
            cursor: not-allowed;
        }

    </style>
</head>
<body>
    <!-- Permission Request Modal -->
    <div id="permission-modal">
        <h2>Welcome!</h2>
        <p>To provide live weather data and camera functionality, this app needs access to your location and camera. Your data will not be stored.</p>
        <button id="permission-button">Allow Access</button>
    </div>

    <!-- Notification container for custom alerts -->
    <div id="notification-container"></div>

    <!-- The container for the live camera feed and image preview -->
    <div class="camera-container">
        <video id="camera-feed" autoplay playsinline></video>
        <img id="image-preview" src="" alt="Uploaded image preview">
    </div>

    <!-- The overlay for all UI controls -->
    <div class="overlay">
        <!-- Top Info Bar -->
        <div class="top-info">
            <button id="reset-button">
                 <img src="back-arrow-icon.png" alt="Back to camera">
            </button>
            <span id="wind-speed">-- mph</span>
            <span id="temperature">-- &deg;C</span>
        </div>
        
        <!-- This empty spacer pushes the footer to the bottom -->
        <div class="grid-spacer"></div>

        <!-- Container for all footer elements -->
        <div class="footer-container">
            <!-- Bottom Camera Controls -->
            <div class="bottom-controls">
                <div id="gallery-button-wrapper">
                    <button class="control-button" id="gallery-button" aria-label="Open Gallery">
                        <img src="<?= BASE_URL ?>/View/Assets/icons/gallery.png" alt="Gallery">
                    </button>
                </div>
                <div class="shutter-button-container">
                    <button class="control-button" id="shutter-button" aria-label="Take Picture"></button>
                    <label for="shutter-button">Analyze</label>
                </div>
                <button class="control-button" id="flashlight-button" aria-label="Toggle Flashlight">
                    <img src="<?= BASE_URL ?>/View/Assets/icons/flashlight_on.png" alt="Flashlight">
                </button>
            </div>
            <!-- Include the bottom navigation bar from your components -->
            <?php require_once __DIR__ . '/../../../View/Components/bottom-nav.php'; ?>
        </div>
    </div>
    
    <!-- Hidden elements for functionality -->
    <canvas id="photo-canvas" style="display:none;"></canvas>
    <input type="file" id="image-upload" accept="image/*" style="display: none;">

    <!-- JS -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // UI Elements
            const permissionModal = document.getElementById('permission-modal');
            const permissionButton = document.getElementById('permission-button');
            const video = document.getElementById('camera-feed');
            const imagePreview = document.getElementById('image-preview');
            const shutterButton = document.getElementById('shutter-button');
            const galleryButton = document.getElementById('gallery-button');
            const flashlightButton = document.getElementById('flashlight-button');
            const flashlightIcon = document.getElementById('flashlight-icon');
            const galleryButtonWrapper = document.getElementById('gallery-button-wrapper');
            const resetButton = document.getElementById('reset-button');
            const imageUpload = document.getElementById('image-upload');
            const canvas = document.getElementById('photo-canvas');
            const windSpeedEl = document.getElementById('wind-speed');
            const temperatureEl = document.getElementById('temperature');
            
            // State
            let currentStream = null;
            let flashlightOn = false;
            let isCameraActive = true;

            // --- Custom Notification Function ---
            function showNotification(message, type = 'info', duration = 4000) {
                const container = document.getElementById('notification-container');
                const notification = document.createElement('div');
                notification.className = `notification ${type}`;
                notification.textContent = message;
                container.appendChild(notification);
                setTimeout(() => {
                    notification.style.opacity = '0';
                    notification.addEventListener('transitionend', () => notification.remove());
                }, duration);
            }

            // --- Weather API Fetch Function ---
            async function fetchWeatherData(lat, lon) {
                const apiUrl = `https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lon}&current=temperature_2m,wind_speed_10m&wind_speed_unit=mph&temperature_unit=celsius`;
                try {
                    const response = await fetch(apiUrl);
                    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                    const data = await response.json();
                    temperatureEl.innerHTML = `${data.current.temperature_2m.toFixed(1)} &deg;C`;
                    windSpeedEl.textContent = `${data.current.wind_speed_10m.toFixed(1)} mph`;
                } catch (error) {
                    console.error("Could not fetch weather data:", error);
                    showNotification("Could not load weather data.", "error");
                }
            }
            
            // --- NEW: Location Request Function ---
            function requestLocation() {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            // Removed location granted notification
                            fetchWeatherData(position.coords.latitude, position.coords.longitude);
                        },
                        (error) => {
                            showNotification("Location access denied. Using default location.", "error");
                            const defaultLat = -6.1352; // North Jakarta
                            const defaultLon = 106.8133;
                            fetchWeatherData(defaultLat, defaultLon);
                        }
                    );
                } else {
                    showNotification("Geolocation is not supported by this browser.", "error");
                    const defaultLat = -6.1352;
                    const defaultLon = 106.8133;
                    fetchWeatherData(defaultLat, defaultLon);
                }
            }

            // --- Permission and Initialization ---
            function requestPermissionsAndStart() {
                permissionModal.style.display = 'none';
                // Start with the camera first, as it's the primary visual element.
                startCamera();
            }

            // --- Camera and View Management ---
            function stopCamera() {
                if (currentStream) {
                    currentStream.getTracks().forEach(track => track.stop());
                    currentStream = null;
                }
            }

            async function startCamera() {
                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    showNotification("Camera API not supported by your browser.", "error");
                    return;
                }
                stopCamera(); // Ensure any previous stream is stopped
                try {
                    // Use facingMode: "environment" for better compatibility
                    const constraints = { video: { facingMode: "environment" }, audio: false };
                    const stream = await navigator.mediaDevices.getUserMedia(constraints);
                    currentStream = stream;
                    video.srcObject = stream;
                    video.style.display = 'block';
                    imagePreview.style.display = 'none';
                    isCameraActive = true;
                    updateControlsForCameraView();

                    // Once camera is successfully started, request location for weather.
                    requestLocation();

                    video.addEventListener('loadedmetadata', () => setTimeout(checkFlashlightCapability, 500));
                } catch (err) {
                    console.error("Error accessing camera with environment mode:", err);
                    // Try fallback: any camera
                    try {
                        const fallbackConstraints = { video: true, audio: false };
                        const stream = await navigator.mediaDevices.getUserMedia(fallbackConstraints);
                        currentStream = stream;
                        video.srcObject = stream;
                        video.style.display = 'block';
                        imagePreview.style.display = 'none';
                        isCameraActive = true;
                        updateControlsForCameraView();

                        requestLocation();

                        video.addEventListener('loadedmetadata', () => setTimeout(checkFlashlightCapability, 500));
                    } catch (fallbackErr) {
                        console.error("Error accessing any camera:", fallbackErr);
                        showNotification("Could not access camera. Please check browser permissions and close other apps using the camera.", "error");
                    }
                }
            }

            function showUploadedImage(dataUrl) {
                stopCamera();
                video.style.display = 'none';
                imagePreview.src = dataUrl;
                imagePreview.style.display = 'block';
                isCameraActive = false;
                updateControlsForImageView();
            }

            // --- UI Updates ---
            function updateControlsForCameraView() {
                resetButton.style.display = 'none';
                galleryButtonWrapper.style.display = 'block';
                checkFlashlightCapability();
            }

            function updateControlsForImageView() {
                resetButton.style.display = 'flex';
                galleryButtonWrapper.style.display = 'none';
                flashlightButton.style.display = 'none';
            }

            // --- Flashlight Control ---
            async function checkFlashlightCapability() {
                flashlightButton.style.display = 'flex';
                flashlightButton.disabled = true;
                if (!currentStream) return;
                try {
                    const track = currentStream.getVideoTracks()[0];
                    const capabilities = track.getCapabilities();
                    if (capabilities.torch) {
                        flashlightButton.disabled = false;
                    }
                } catch (e) {
                    console.error("Could not check flashlight capabilities", e);
                }
            }

            async function toggleFlashlight() {
                if (!currentStream) return;
                const track = currentStream.getVideoTracks()[0];
                try {
                    // Toggle flashlight state
                    flashlightOn = !flashlightOn;
                    await track.applyConstraints({ advanced: [{ torch: flashlightOn }] });
                    flashlightIcon.src = flashlightOn ? 'flashlight-on-icon.png' : 'flashlight-off-icon.png';
                } catch (err) {
                    console.error("Failed to toggle flashlight:", err);
                    showNotification("Flashlight not supported on this device/browser.", "error");
                }
            }

            // --- Analysis / Photo Capture ---
            function analyzeContent() {
                const context = canvas.getContext('2d');
                let source, width, height;

                if (isCameraActive) {
                    if (!currentStream) {
                        showNotification("Camera is not active.", "error");
                        return;
                    }
                    source = video;
                    width = video.videoWidth;
                    height = video.videoHeight;
                } else {
                    source = imagePreview;
                    width = imagePreview.naturalWidth;
                    height = imagePreview.naturalHeight;
                }

                canvas.width = width;
                canvas.height = height;
                context.drawImage(source, 0, 0, width, height);
                
                const imageDataUrl = canvas.toDataURL('image/jpeg');
                console.log("Analysis initiated. Data URL:", imageDataUrl.substring(0, 50) + "...");
                showNotification("habis itu muncul analysis animation baru ke form", "success");
            }

            // --- Event Listeners ---
            permissionButton.addEventListener('click', requestPermissionsAndStart);
            shutterButton.addEventListener('click', analyzeContent);
            galleryButton.addEventListener('click', () => imageUpload.click());
            imageUpload.addEventListener('change', (event) => {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = (e) => showUploadedImage(e.target.result);
                    reader.readAsDataURL(file);
                }
                event.target.value = null; // Reset input for same-file selection
            });
            resetButton.addEventListener('click', startCamera);
            flashlightButton.addEventListener('click', toggleFlashlight);

        });
    </script>
</body>
</html>
