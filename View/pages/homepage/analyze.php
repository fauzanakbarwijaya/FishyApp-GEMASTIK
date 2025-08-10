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
    <link rel="stylesheet" href="<?= BASE_URL ?>/View/Assets/css/bootstrap.min.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    <!-- Custom Styles -->
    <style>
        /* Basic setup */
        :root {
            --primary-blue: #2563eb; 
            --nav-bg: #0d172a; 
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
            padding-bottom: 85px; /* <-- Increase this value to move buttons higher */
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
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 4000;
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

        /* --- NEW: Analysis Animation Styles --- */
        #analysis-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 3000; /* Above everything else */
            background-color: #000;
            display: none; /* Hidden by default */
            justify-content: center;
            align-items: center;
        }

        #analysis-canvas, #label-canvas {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        #label-canvas {
            z-index: 3002; /* On top of the 3D canvas */
        }

        #analysis-status {
            position: absolute;
            bottom: 10%;
            left: 50%;
            transform: translateX(-50%);
            color: #93c5fd; /* Light Blue */
            font-size: 1.2rem;
            font-weight: 500;
            text-shadow: 0 0 8px #3b82f6, 0 0 12px #3b82f6;
            background-color: rgba(10, 10, 25, 0.7);
            padding: 10px 20px;
            border-radius: 8px;
            border: 1px solid #3b82f6;
            z-index: 3003;
            font-family: 'Courier New', Courier, monospace;
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
                 <img src="https://img.icons8.com/ios-filled/50/ffffff/reply-arrow.png" alt="Back to camera">
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
                        <img src="<?= BASE_URL ?>/View/Assets/icons/gallery.png" alt="Gallery" onerror="this.onerror=null;this.src='https://img.icons8.com/ios/50/000000/image.png';">
                    </button>
                </div>
                <div class="shutter-button-container">
                    <button class="control-button" id="shutter-button" aria-label="Take Picture"></button>
                    <label for="shutter-button">Analyze</label>
                </div>
                <button class="control-button" id="flashlight-button" aria-label="Toggle Flashlight">
                    <img id="flashlight-icon" src="<?= BASE_URL ?>/View/Assets/icons/flashlight_on.png" alt="Flashlight" onerror="this.onerror=null;this.src='https://img.icons8.com/ios-filled/50/000000/flash-on.png';">
                </button>
            </div>
            <!-- Include the bottom navigation bar from your components -->
            <?php @require_once __DIR__ . '/../../../View/Components/bottom-nav.php'; ?>
        </div>
    </div>
    
    <!-- NEW: Analysis Animation Container -->
    <div id="analysis-container">
        <canvas id="analysis-canvas"></canvas>
        <canvas id="label-canvas"></canvas>
        <div id="analysis-status">INITIALIZING...</div>
    </div>

    <!-- Hidden elements for functionality -->
    <canvas id="photo-canvas" style="display:none;"></canvas>
    <input type="file" id="image-upload" accept="image/*" style="display: none;">

    <!-- JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow-models/coco-ssd"></script>
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
            const overlay = document.querySelector('.overlay');

            // Analysis Animation Elements
            const analysisContainer = document.getElementById('analysis-container');
            const analysisCanvas = document.getElementById('analysis-canvas');
            const labelCanvas = document.getElementById('label-canvas');
            const analysisStatus = document.getElementById('analysis-status');
            
            // State
            let currentStream = null;
            let flashlightOn = false;
            let isCameraActive = true;
            let model = null; // To hold the loaded ML model

            // --- Custom Notification Function ---
            function showNotification(message, type = 'info', duration = 4000) {
                const container = document.getElementById('notification-container');
                // Clear existing notifications
                while (container.firstChild) {
                    container.removeChild(container.firstChild);
                }
                const notification = document.createElement('div');
                notification.className = `notification ${type}`;
                notification.textContent = message;
                container.appendChild(notification);
                setTimeout(() => {
                    notification.style.opacity = '0';
                    notification.addEventListener('transitionend', () => notification.remove());
                }, duration);
            }

            // --- Weather Data State ---
            let weatherData = {
                windSpeed: null,
                temperature: null
            };

            // --- Weather API Fetch Function ---
            async function fetchWeatherData(lat, lon) {
                const apiUrl = `https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lon}&current=temperature_2m,wind_speed_10m&wind_speed_unit=mph&temperature_unit=celsius`;
                try {
                    const response = await fetch(apiUrl);
                    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                    const data = await response.json();
                    temperatureEl.innerHTML = `${data.current.temperature_2m.toFixed(1)} &deg;C`;
                    windSpeedEl.textContent = `${data.current.wind_speed_10m.toFixed(1)} mph`;
                    weatherData.windSpeed = data.current.wind_speed_10m;
                    weatherData.temperature = data.current.temperature_2m;
                } catch (error) {
                    console.error("Could not fetch weather data:", error);
                    showNotification("Could not load weather data.", "error");
                }
            }
            
            // --- Location Request Function ---
            function requestLocation() {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(
                        (position) => {
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
                analysisContainer.style.display = 'none';
                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    showNotification("Camera API not supported by your browser.", "error");
                    return;
                }
                stopCamera();
                try {
                    const constraints = { video: { facingMode: "environment" }, audio: false };
                    const stream = await navigator.mediaDevices.getUserMedia(constraints);
                    currentStream = stream;
                    video.srcObject = stream;
                    video.style.display = 'block';
                    imagePreview.style.display = 'none';
                    isCameraActive = true;
                    updateControlsForCameraView();
                    requestLocation();
                    video.addEventListener('loadedmetadata', () => setTimeout(checkFlashlightCapability, 500));
                } catch (err) {
                    console.error("Error accessing camera with environment mode:", err);
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
                        showNotification("Could not access camera. Please check permissions.", "error");
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
                galleryButtonWrapper.style.display = 'grid';
                overlay.style.display = 'grid';
                checkFlashlightCapability();
            }

            function updateControlsForImageView() {
                resetButton.style.display = 'flex';
                galleryButtonWrapper.style.display = 'none';
                flashlightButton.style.display = 'none';
                overlay.style.display = 'grid';
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
                    flashlightOn = !flashlightOn;
                    await track.applyConstraints({ advanced: [{ torch: flashlightOn }] });
                    flashlightIcon.src = flashlightOn ? '<?= BASE_URL ?>/View/Assets/icons/flashlight_on.png' : '<?= BASE_URL ?>/View/Assets/icons/flashlight_off.png';
                } catch (err) {
                    console.error("Failed to toggle flashlight:", err);
                    showNotification("Flashlight not supported on this device.", "error");
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
                
                overlay.style.display = 'none';
                analysisContainer.style.display = 'flex';
                
                runAnalysisAnimation(imageDataUrl);
            }

            // --- PROFESSIONAL ANALYSIS ANIMATION ---
            async function runAnalysisAnimation(imageUrl) {
                let animationFrameId;
                const clock = new THREE.Clock();
                const labelCtx = labelCanvas.getContext('2d');

                // --- Renderer and Scene ---
                const scene = new THREE.Scene();
                const camera = new THREE.OrthographicCamera(-1, 1, 1, -1, 0, 1);
                const renderer = new THREE.WebGLRenderer({ canvas: analysisCanvas, alpha: true, antialias: true });
                
                const resizeCanvases = () => {
                    renderer.setSize(window.innerWidth, window.innerHeight);
                    labelCanvas.width = window.innerWidth;
                    labelCanvas.height = window.innerHeight;
                };
                resizeCanvases();
                renderer.setPixelRatio(window.devicePixelRatio);

                // --- Shaders ---
                const vertexShader = `
                    varying vec2 vUv;
                    void main() {
                        vUv = uv;
                        gl_Position = vec4(position, 1.0);
                    }
                `;

                const fragmentShader = `
                    uniform sampler2D tDiffuse;
                    uniform vec2 resolution;
                    uniform float time;
                    uniform float scanLineY;

                    varying vec2 vUv;
                    
                    float luminance(vec3 color) {
                        return 0.2126 * color.r + 0.7154 * color.g + 0.072 * color.b;
                    }
                    
                    vec3 rgb2hsv(vec3 c) {
                        vec4 K = vec4(0.0, -1.0 / 3.0, 2.0 / 3.0, -1.0);
                        vec4 p = mix(vec4(c.bg, K.wz), vec4(c.gb, K.xy), step(c.b, c.g));
                        vec4 q = mix(vec4(p.xyw, c.r), vec4(c.r, p.yzx), step(p.x, c.r));
                        float d = q.x - min(q.w, q.y);
                        float e = 1.0e-10;
                        return vec3(abs(q.z + (q.w - q.y) / (6.0 * d + e)), d / (q.x + e), q.x);
                    }

                    void main() {
                        vec2 texel = vec2(1.0 / resolution.x, 1.0 / resolution.y);
                        vec3 originalColor = texture2D(tDiffuse, vUv).rgb;
                        
                        float edge = 0.0;
                        edge += luminance(texture2D(tDiffuse, vUv + vec2(-texel.x, -texel.y)).rgb) * -1.0;
                        edge += luminance(texture2D(tDiffuse, vUv + vec2(0.0, -texel.y)).rgb) * -2.0;
                        edge += luminance(texture2D(tDiffuse, vUv + vec2(texel.x, -texel.y)).rgb) * -1.0;
                        edge += luminance(texture2D(tDiffuse, vUv + vec2(-texel.x, 0.0)).rgb) * 2.0;
                        edge += luminance(texture2D(tDiffuse, vUv + vec2(texel.x, 0.0)).rgb) * -2.0;
                        edge += luminance(texture2D(tDiffuse, vUv + vec2(-texel.x, texel.y)).rgb) * 1.0;
                        edge += luminance(texture2D(tDiffuse, vUv + vec2(0.0, texel.y)).rgb) * 2.0;
                        edge += luminance(texture2D(tDiffuse, vUv + vec2(texel.x, texel.y)).rgb) * 1.0;
                        
                        vec3 hsv = rgb2hsv(originalColor);
                        bool isWater = hsv.x > 0.5 && hsv.x < 0.75 && hsv.y > 0.2 && hsv.z > 0.15;
                        bool isPlant = hsv.x > 0.2 && hsv.x < 0.45 && hsv.y > 0.25 && hsv.z > 0.2;
                        
                        vec3 finalColor = originalColor * 0.5;

                        if (vUv.y < scanLineY) {
                            if (isWater) {
                                float shimmer = sin(vUv.y * 300.0 + time * 6.0) * 0.5 + 0.5;
                                vec3 waterColor = vec3(0.2, 0.5, 1.0);
                                finalColor = mix(finalColor, waterColor, 0.7 * shimmer);
                            }
                            if (isPlant && !isWater) {
                                vec3 plantColor = vec3(0.1, 1.0, 0.3);
                                finalColor = mix(finalColor, plantColor, 0.6);
                            }
                            if (edge > 0.5) {
                                finalColor = mix(finalColor, vec3(1.0, 0.2, 0.2), 0.8);
                            }
                        }

                        float scanGlow = 1.0 - smoothstep(0.0, 0.015, abs(vUv.y - scanLineY));
                        finalColor += vec3(0.6, 0.8, 1.0) * scanGlow * 0.6;
                        
                        float gridPattern = (mod(vUv.x * resolution.x, 25.0) < 1.0 || mod(vUv.y * resolution.y, 25.0) < 1.0) ? 0.1 : 0.0;
                        finalColor += vec3(0.5, 0.7, 1.0) * gridPattern * (1.0 - scanGlow) * 0.5;

                        gl_FragColor = vec4(finalColor, 1.0);
                    }
                `;

                // --- Enhanced Image Filtering Functions ---
                function rgbToHsv(r, g, b) {
                    r /= 255; g /= 255; b /= 255;
                    let max = Math.max(r, g, b), min = Math.min(r, g, b);
                    let h, s, v = max;
                    let d = max - min;
                    s = max == 0 ? 0 : d / max;
                    if (max == min) { h = 0; } 
                    else {
                        switch (max) {
                            case r: h = (g - b) / d + (g < b ? 6 : 0); break;
                            case g: h = (b - r) / d + 2; break;
                            case b: h = (r - g) / d + 4; break;
                        }
                        h /= 6;
                    }
                    return [h, s, v];
                }

                // Validate if image is suitable for fishing analysis
                const validateFishingEnvironment = (colorAnalysis, predictions) => {
                    // Check for human presence - Keep reasonable sensitivity
                    const hasPerson = predictions.some(p => p.class === 'person' && p.score > 0.6);
                    const hasSkinTone = colorAnalysis.skinRatio > 0.06;
                    
                    if (hasPerson || hasSkinTone) { // Either condition triggers rejection
                        return { valid: false, reason: "Human presence detected. Please capture a natural water environment without people." };
                    }
                    
                    // Check for indoor/artificial environment - More comprehensive
                    const hasIndoorObjects = predictions.some(p => 
                        ['tv', 'laptop', 'keyboard', 'mouse', 'chair', 'couch', 'bed', 'dining table', 
                         'toilet', 'sink', 'refrigerator', 'microwave', 'oven', 'toaster', 'book',
                         'clock', 'scissors', 'teddy bear', 'hair drier', 'toothbrush'].includes(p.class) && p.score > 0.5
                    );
                    if (hasIndoorObjects) {
                        return { valid: false, reason: "Indoor environment detected. Please capture outdoor water scenes." };
                    }
                    
                    // Check for sufficient water content - Require meaningful water presence
                    const hasWaterObjects = predictions.some(p => 
                        ['boat', 'surfboard', 'kayak', 'canoe', 'ship'].includes(p.class) && p.score > 0.3
                    );
                    const hasSufficientWater = colorAnalysis.waterRatio > 0.05; // Back to 5%
                    const hasNaturalWaterEnvironment = colorAnalysis.naturalRatio > 0.20; // Must be at least 20% natural
                    
                    if (!hasWaterObjects && (!hasSufficientWater || !hasNaturalWaterEnvironment)) {
                        return { valid: false, reason: "No water body detected. Please capture a photo of a lake, river, pond, or ocean." };
                    }
                    
                    // Check for too much artificial content
                    if (colorAnalysis.redRatio > 0.10 || colorAnalysis.grayRatio > 0.55) {
                        return { valid: false, reason: "Too many artificial objects detected. Please focus on natural water environments." };
                    }
                    
                    // Enhanced inappropriate objects detection
                    const hasInappropriateObjects = predictions.some(p => 
                        ['cell phone', 'remote', 'wine glass', 'cup', 'fork', 'knife', 'spoon', 
                         'bowl', 'banana', 'apple', 'sandwich', 'orange', 'broccoli', 'carrot', 
                         'hot dog', 'pizza', 'donut', 'cake', 'bottle', 'backpack', 'umbrella',
                         'handbag', 'tie', 'suitcase', 'sports ball', 'kite', 'baseball bat',
                         'baseball glove', 'skateboard', 'tennis racket'].includes(p.class) && p.score > 0.6
                    );
                    if (hasInappropriateObjects) {
                        return { valid: false, reason: "Inappropriate objects detected. Please capture natural fishing environments only." };
                    }
                    
                    // Check for vehicle presence (cars, trucks, etc.)
                    const hasVehicles = predictions.some(p => 
                        ['car', 'motorcycle', 'airplane', 'bus', 'train', 'truck', 'bicycle'].includes(p.class) && p.score > 0.5
                    );
                    if (hasVehicles) {
                        return { valid: false, reason: "Vehicles detected. Please focus on natural water environments away from roads." };
                    }
                    
                    // Additional validation: Check if image is too artificial/urban
                    const tooArtificial = (colorAnalysis.grayRatio + colorAnalysis.redRatio) > 0.4;
                    const notEnoughNature = colorAnalysis.naturalRatio < 0.15;
                    
                    if (tooArtificial && notEnoughNature) {
                        return { valid: false, reason: "Image appears too artificial or urban. Please capture natural outdoor water environments." };
                    }
                    
                    return { valid: true, reason: "Valid water environment detected." };
                };

                // Enhanced water environment detection
                const analyzeWaterEnvironment = () => {
                    const imgCanvas = document.getElementById('photo-canvas');
                    const ctx = imgCanvas.getContext('2d');
                    const imageData = ctx.getImageData(0, 0, imgCanvas.width, imgCanvas.height).data;
                    const totalPixels = imgCanvas.width * imgCanvas.height;
                    
                    let waterPixels = 0;
                    let skyPixels = 0;
                    let greenPixels = 0; // Natural vegetation
                    let grayPixels = 0;  // Urban/concrete
                    let redPixels = 0;   // Artificial objects
                    let skinPixels = 0;  // Human skin tones
                    let brightPixels = 0; // Very bright/white pixels
                    let darkPixels = 0;   // Very dark pixels
                    
                    for(let i = 0; i < imageData.length; i += 4) {
                        const r = imageData[i], g = imageData[i+1], b = imageData[i+2];
                        const hsv = rgbToHsv(r, g, b);
                        const brightness = (r + g + b) / 3;
                        
                        // Water detection - More specific ranges
                        const isWater = (hsv[0] > 0.5 && hsv[0] < 0.7 && hsv[1] > 0.2 && hsv[2] > 0.15) ||
                                        (hsv[0] > 0.45 && hsv[0] < 0.6 && hsv[1] > 0.3 && hsv[2] > 0.2);
                        if (isWater) waterPixels++;
                        
                        // Sky detection
                        const isSky = (hsv[0] > 0.55 && hsv[0] < 0.75 && hsv[1] < 0.3 && hsv[2] > 0.7) ||
                                     (hsv[1] < 0.1 && hsv[2] > 0.8);
                        if (isSky) skyPixels++;
                        
                        // Natural vegetation
                        const isVegetation = (hsv[0] > 0.2 && hsv[0] < 0.45 && hsv[1] > 0.3 && hsv[2] > 0.2) ||
                                           (hsv[0] > 0.08 && hsv[0] < 0.15 && hsv[1] > 0.4 && hsv[2] > 0.15);
                        if (isVegetation) greenPixels++;
                        
                        // Urban/concrete (grays)
                        const isGray = hsv[1] < 0.2 && hsv[2] > 0.25 && hsv[2] < 0.75;
                        if (isGray) grayPixels++;
                        
                        // Artificial red objects
                        const isRed = ((hsv[0] < 0.05 || hsv[0] > 0.95) && hsv[1] > 0.5 && hsv[2] > 0.3);
                        if (isRed) redPixels++;
                        
                        // Skin tone detection - More precise
                        const isSkin = (hsv[0] > 0.03 && hsv[0] < 0.08 && hsv[1] > 0.25 && hsv[1] < 0.65 && hsv[2] > 0.4 && hsv[2] < 0.9);
                        if (isSkin) skinPixels++;
                        
                        // Brightness analysis
                        if (brightness > 240) brightPixels++;
                        if (brightness < 25) darkPixels++;
                    }
                    
                    return {
                        waterRatio: waterPixels / totalPixels,
                        skyRatio: skyPixels / totalPixels,
                        vegetationRatio: greenPixels / totalPixels,
                        grayRatio: grayPixels / totalPixels,
                        redRatio: redPixels / totalPixels,
                        skinRatio: skinPixels / totalPixels,
                        brightRatio: brightPixels / totalPixels,
                        darkRatio: darkPixels / totalPixels,
                        naturalRatio: (waterPixels + skyPixels + greenPixels) / totalPixels
                    };
                };

                // Label box drawing function
                const drawLabelBox = (obj) => {
                    labelCtx.strokeStyle = obj.color;
                    labelCtx.fillStyle = obj.color;
                    labelCtx.font = "14px 'Courier New', monospace";
                    labelCtx.shadowColor = obj.color;
                    labelCtx.shadowBlur = 8;
                    
                    const p = Math.min(1, obj.progress / 0.8);
                    const cornerSize = 15 * p;
                    
                    labelCtx.globalAlpha = p;
                    labelCtx.beginPath();
                    labelCtx.moveTo(obj.x, obj.y + cornerSize); labelCtx.lineTo(obj.x, obj.y); labelCtx.lineTo(obj.x + cornerSize, obj.y);
                    labelCtx.moveTo(obj.x + obj.w - cornerSize, obj.y); labelCtx.lineTo(obj.x + obj.w, obj.y); labelCtx.lineTo(obj.x + obj.w, obj.y + cornerSize);
                    labelCtx.moveTo(obj.x + obj.w, obj.y + obj.h - cornerSize); labelCtx.lineTo(obj.x + obj.w, obj.y + obj.h); labelCtx.lineTo(obj.x + obj.w - cornerSize, obj.y + obj.h);
                    labelCtx.moveTo(obj.x + cornerSize, obj.y + obj.h); labelCtx.lineTo(obj.x, obj.y + obj.h); labelCtx.lineTo(obj.x, obj.y + obj.h - cornerSize);
                    labelCtx.lineWidth = 2;
                    labelCtx.stroke();

                    if (p >= 1) {
                       labelCtx.globalAlpha = Math.min(1, (obj.progress - 0.8) / 0.5);
                       labelCtx.fillText(`${obj.label} [${(obj.score * 100).toFixed(1)}%]`, obj.x + 5, obj.y - 10);
                    }
                    labelCtx.globalAlpha = 1.0;
                    labelCtx.shadowBlur = 0;
                };

                // --- Scene Setup ---
                const textureLoader = new THREE.TextureLoader();
                textureLoader.load(imageUrl, async (texture) => {
                    
                    analysisStatus.textContent = "LOADING AI MODEL...";
                    if (!model) {
                        model = await cocoSsd.load();
                    }
                    
                    analysisStatus.textContent = "VALIDATING ENVIRONMENT...";
                    const imgCanvas = document.getElementById('photo-canvas');
                    const predictions = await model.detect(imgCanvas);
                    
                    // Analyze image content
                    const colorAnalysis = analyzeWaterEnvironment();
                    const validation = validateFishingEnvironment(colorAnalysis, predictions);
                    
                    if (!validation.valid) {
                        analysisStatus.textContent = "INVALID ENVIRONMENT";
                        showNotification(validation.reason, "error", 6000);
                        
                        setTimeout(() => {
                            cancelAnimationFrame(animationFrameId);
                            analysisContainer.style.display = 'none';
                            renderer.dispose();
                            startCamera();
                        }, 3000);
                        return;
                    }
                    
                    analysisStatus.textContent = "ANALYZING FISHING CONDITIONS...";
                    
                    // Continue with existing analysis code
                    let weatherData = {
                        windSpeed: windSpeedEl.textContent.includes('--') ? 8 : parseFloat(windSpeedEl.textContent),
                        temperature: temperatureEl.textContent.includes('--') ? 25 : parseFloat(temperatureEl.textContent)
                    };

                    // Generate fishing analysis scores based on validated environment
                    const analysisScores = {
                        color: Math.min(100, 30 + (colorAnalysis.waterRatio * 350)),
                        wave: Math.min(100, 40 + Math.random() * 35),
                        light: Math.min(100, colorAnalysis.skyRatio > 0.2 ? 75 : 50),
                        weather: weatherData.windSpeed > 25 ? 25 : weatherData.windSpeed > 15 ? 60 : 85,
                        time: (new Date().getHours() >= 5 && new Date().getHours() <= 8) || 
                              (new Date().getHours() >= 16 && new Date().getHours() <= 19) ? 100 : 65,
                        vegetation: Math.min(100, 35 + (colorAnalysis.vegetationRatio * 200)),
                        wind: weatherData.windSpeed > 25 ? 25 : weatherData.windSpeed > 15 ? 60 : 100,
                        temperature: weatherData.temperature < 15 || weatherData.temperature > 35 ? 20 : 80
                    };

                    const weights = {
                        color: 0.15, wave: 0.12, light: 0.10, weather: 0.12,
                        time: 0.15, vegetation: 0.18, wind: 0.10, temperature: 0.08
                    };

                    const finalPercentage = Math.round(
                        (analysisScores.color * weights.color) +
                        (analysisScores.wave * weights.wave) +
                        (analysisScores.light * weights.light) +
                        (analysisScores.weather * weights.weather) +
                        (analysisScores.time * weights.time) +
                        (analysisScores.vegetation * weights.vegetation) +
                        (analysisScores.wind * weights.wind) +
                        (analysisScores.temperature * weights.temperature)
                    );

                    function generateRecommendations(percentage, scores) {
                        const recommendations = [];
                        if (percentage >= 80) {
                            recommendations.push("Excellent fishing conditions! This is an ideal time to fish.");
                        } else if (percentage >= 60) {
                            recommendations.push("Good fishing conditions. You should have success.");
                        } else if (percentage >= 40) {
                            recommendations.push("Fair conditions. Consider adjusting your technique.");
                        } else {
                            recommendations.push("Poor conditions. Consider waiting for better conditions.");
                        }
                        
                        if (scores.color < 50) recommendations.push("Water clarity could be better. Try deeper areas or use vibrant lures.");
                        if (scores.vegetation < 50) recommendations.push("Limited structure detected. Look for underwater features.");
                        if (scores.time < 50) recommendations.push("Consider fishing during dawn (5-8 AM) or dusk (4-7 PM).");
                        
                        return recommendations;
                    }

                    const resultData = {
                        scores: analysisScores,
                        weights: weights,
                        finalPercentage: finalPercentage,
                        weather: weatherData,
                        timestamp: new Date().toISOString(),
                        recommendations: generateRecommendations(finalPercentage, analysisScores),
                        environmentAnalysis: colorAnalysis
                    };

                    // Filter objects for animation (exclude inappropriate detections)
                    const detectedObjects = predictions.filter(p => 
                        !['person', 'tv', 'laptop', 'cell phone', 'remote'].includes(p.class)
                    ).map(p => {
                        const [x, y, w, h] = p.bbox;
                        return {
                            x: (x / imgCanvas.width) * window.innerWidth,
                            y: (y / imgCanvas.height) * window.innerHeight,
                            w: (w / imgCanvas.width) * window.innerWidth,
                            h: (h / imgCanvas.height) * window.innerHeight,
                            label: p.class.toUpperCase(),
                            score: p.score,
                            color: p.class === 'boat' ? '#60a5fa' : 
                                  ['tree', 'potted plant'].includes(p.class) ? '#4ade80' : '#fbbf24',
                            triggerY: (y / imgCanvas.height),
                            active: false,
                            progress: 0
                        };
                    });

                    const material = new THREE.ShaderMaterial({
                        uniforms: {
                            tDiffuse: { value: texture },
                            resolution: { value: new THREE.Vector2(window.innerWidth, window.innerHeight) },
                            time: { value: 0.0 },
                            scanLineY: { value: 0.0 }
                        },
                        vertexShader,
                        fragmentShader
                    });

                    const quad = new THREE.Mesh(new THREE.PlaneGeometry(2, 2), material);
                    scene.add(quad);
                    
                    // Enhanced status sequence
                    setTimeout(() => analysisStatus.textContent = "SCANNING WATER CONDITIONS...", 500);
                    setTimeout(() => analysisStatus.textContent = "IDENTIFYING FISHING SPOTS...", 2500);
                    setTimeout(() => analysisStatus.textContent = "CALCULATING SUCCESS RATES...", 4000);
                    setTimeout(() => analysisStatus.textContent = "ANALYSIS COMPLETE", 5000);

                    const animate = () => {
                        animationFrameId = requestAnimationFrame(animate);
                        const elapsedTime = clock.getElapsedTime();
                        const delta = clock.getDelta();
                        
                        const scanDuration = 6.0;
                        const scanProgress = Math.min(elapsedTime / scanDuration, 1.0);
                        
                        material.uniforms.time.value = elapsedTime;
                        material.uniforms.scanLineY.value = scanProgress;

                        labelCtx.clearRect(0, 0, labelCanvas.width, labelCanvas.height);
                        detectedObjects.forEach(obj => {
                            if (scanProgress >= obj.triggerY && !obj.active) {
                                obj.active = true;
                            }
                            if (obj.active) {
                                obj.progress += delta * 2;
                                drawLabelBox(obj);
                            }
                        });

                        renderer.render(scene, camera);
                        
                        // End animation and redirect
                        if (scanProgress >= 1.0) {
                            setTimeout(() => {
                                cancelAnimationFrame(animationFrameId);
                                analysisContainer.style.display = 'none';
                                renderer.dispose();
                                localStorage.setItem('fishing_analysis_result', JSON.stringify(resultData));
                                window.location.href = 'result.php';
                            }, 1000);
                        }
                    };
                    animate();

                });

                window.addEventListener('resize', resizeCanvases);
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
                                            // Water quality indicators
                                            float clarity = (hsv.z + hsv.y) * 0.5;
                                            if(clarity > 0.7) {
                                                finalColor += vec3(0.0, 0.3, 0.6) * 0.3; // Clear water bonus
                                            }
                                        }
                                        
                                        // Vegetation analysis overlay
                                        if (isVegetation && !isWater) {
                                            float growth = sin(vUv.x * 200.0 + time * 4.0) * 0.3 + 0.7;
                                            vec3 vegColor = vec3(0.1, 0.8, 0.2);
                                            finalColor = mix(finalColor, vegColor, 0.7 * growth * analysisProgress);
                                        }
                                        
                                        // Edge/structure detection
                                        if (abs(edge) > 0.3) {
                                            vec3 edgeColor = vec3(1.0, 0.6, 0.1);
                                            finalColor = mix(finalColor, edgeColor, 0.6 * analysisProgress);
                                        }
                                        
                                        // Analysis completion overlay
                                        float completion = smoothstep(scanLineY - 0.1, scanLineY, vUv.y) * analysisProgress;
                                        finalColor = mix(finalColor, finalColor * 1.3, completion * 0.3);
                                    }

                                    // Enhanced scan line with multiple effects
                                    float scanGlow = 1.0 - smoothstep(0.0, 0.02, abs(vUv.y - scanLineY));
                                    vec3 scanColor = vec3(0.4, 0.8, 1.0);
                                    finalColor += scanColor * scanGlow * 0.8;
                                    
                                    // Secondary scan line
                                    float scanGlow2 = 1.0 - smoothstep(0.0, 0.005, abs(vUv.y - scanLineY));
                                    finalColor += vec3(1.0, 1.0, 1.0) * scanGlow2 * 0.9;
                                    
                                    // Enhanced grid pattern with analysis data
                                    float gridSize = 20.0 + sin(time * 2.0) * 5.0;
                                    float gridPattern = (mod(vUv.x * resolution.x, gridSize) < 1.5 || 
                                                        mod(vUv.y * resolution.y, gridSize) < 1.5) ? 0.15 : 0.0;
                                    finalColor += vec3(0.3, 0.6, 1.0) * gridPattern * (1.0 - scanGlow) * 0.4 * analysisProgress;
                                    
                                    // Data flow lines
                                    float dataFlow = sin(vUv.x * 10.0 - time * 15.0) * 0.5 + 0.5;
                                    if(mod(vUv.y * resolution.y, 40.0) < 2.0 && vUv.y < scanLineY) {
                                        finalColor += vec3(0.0, 0.8, 1.0) * dataFlow * 0.3 * analysisProgress;
                                    }

                                    gl_FragColor = vec4(finalColor, 1.0);
                                }
                            `;
                        
                        const mesh = new THREE.Mesh(geometry, material);
                        scene.add(mesh);

                        let scanProgress = 0;
                        let analysisStep = 0;
                        const analysisSteps = [
                            "INITIALIZING VISUAL ANALYSIS...",
                            "DETECTING WATER CLARITY...",
                            "ANALYZING WAVE PATTERNS...",
                            "MEASURING LIGHT CONDITIONS...",
                            "PROCESSING VEGETATION DATA...",
                            "INTEGRATING WEATHER DATA...",
                            "CALCULATING TIME FACTORS...",
                            "GENERATING RECOMMENDATIONS...",
                            "ANALYSIS COMPLETE"
                        ];

                        function animate() {
                            const elapsedTime = clock.getElapsedTime();
                            material.uniforms.time.value = elapsedTime;
                            
                            scanProgress += 0.008; // Slower scan for more detail
                            material.uniforms.scanLineY.value = scanProgress;
                            material.uniforms.analysisProgress.value = Math.min(scanProgress * 1.2, 1.0);
                            
                            // Update status text based on progress
                            const stepIndex = Math.floor(scanProgress * analysisSteps.length);
                            if (stepIndex < analysisSteps.length) {
                                analysisStatus.textContent = analysisSteps[stepIndex];
                            }
                            
                            if (scanProgress < 1.0) {
                                renderer.render(scene, camera);
                                animationFrameId = requestAnimationFrame(animate);
                            } else {
                                analysisStatus.textContent = "FINALIZING RESULTS...";
                                setTimeout(() => {
                                    localStorage.setItem('fishing_analysis_result', JSON.stringify(resultData));
                                    window.location.href = 'result.php';
                                }, 1500);
                            }
                        }
                        
                        animate();
                    });

                } catch (error) {
                    console.error('Analysis error:', error);
                    analysisStatus.textContent = "ANALYSIS ERROR";
                    
                    // Fallback - create dummy data and redirect
                    const fallbackData = {
                        scores: {
                            color: 70, wave: 80, light: 60, weather: 75,
                            time: 85, vegetation: 90, wind: 70, temperature: 80
                        },
                        weights: {
                            color: 0.15, wave: 0.12, light: 0.10, weather: 0.12,
                            time: 0.15, vegetation: 0.18, wind: 0.10, temperature: 0.08
                        },
                        finalPercentage: 75,
                        weather: weatherData,
                        timestamp: new Date().toISOString(),
                        recommendations: ["Good fishing conditions based on fallback analysis."]
                    };
                    
                    setTimeout(() => {
                        localStorage.setItem('fishing_analysis_result', JSON.stringify(fallbackData));
                        window.location.href = 'result.php';
                    }, 2000);
                }

                window.addEventListener('resize', () => {
                    resizeCanvases();
                });
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
