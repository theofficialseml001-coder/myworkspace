// NaturalReader Clone - Text to Speech Application

document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements
    const textInput = document.getElementById('text-input');
    const playBtn = document.getElementById('play-btn');
    const pauseBtn = document.getElementById('pause-btn');
    const stopBtn = document.getElementById('stop-btn');
    const speedSlider = document.getElementById('speed-slider');
    const speedValue = document.getElementById('speed-value');
    const voiceSelect = document.getElementById('voice-select');
    const progressFill = document.getElementById('progress-fill');

    // Speech Synthesis variables
    let synth = window.speechSynthesis;
    let voices = [];
    let utterance = null;
    let isPaused = false;
    let isPlaying = false;

    // Initialize voices
    function loadVoices() {
        voices = synth.getVoices();
        
        // Clear existing options except the first one
        while (voiceSelect.options.length > 1) {
            voiceSelect.remove(1);
        }

        // Add voices to selector
        voices.forEach((voice, index) => {
            const option = document.createElement('option');
            option.value = index;
            option.textContent = `${voice.name} (${voice.lang})`;
            if (voice.default) {
                option.selected = true;
            }
            voiceSelect.appendChild(option);
        });

        // If no voices loaded yet, try again
        if (voices.length === 0) {
            setTimeout(loadVoices, 100);
        }
    }

    // Load voices on page load
    loadVoices();
    
    // Chrome loads voices asynchronously
    if (speechSynthesis.onvoiceschanged !== undefined) {
        speechSynthesis.onvoiceschanged = loadVoices;
    }

    // Speed control
    speedSlider.addEventListener('input', function() {
        speedValue.textContent = this.value + 'x';
        if (utterance) {
            utterance.rate = parseFloat(this.value);
        }
    });

    // Play button
    playBtn.addEventListener('click', function() {
        if (isPaused) {
            // Resume paused speech
            synth.resume();
            isPaused = false;
            toggleControls(true);
        } else if (!isPlaying) {
            // Start new speech
            speakText();
        }
    });

    // Pause button
    pauseBtn.addEventListener('click', function() {
        if (isPlaying && !isPaused) {
            synth.pause();
            isPaused = true;
            toggleControls(false);
        }
    });

    // Stop button
    stopBtn.addEventListener('click', function() {
        if (isPlaying || isPaused) {
            synth.cancel();
            isPlaying = false;
            isPaused = false;
            toggleControls(false);
            progressFill.style.width = '0%';
        }
    });

    // Main speak function
    function speakText() {
        const text = textInput.value.trim();
        
        if (!text) {
            alert('Please enter some text to read.');
            return;
        }

        // Cancel any ongoing speech
        synth.cancel();

        // Create new utterance
        utterance = new SpeechSynthesisUtterance(text);
        
        // Set selected voice
        const selectedVoiceIndex = voiceSelect.value;
        if (selectedVoiceIndex && voices[selectedVoiceIndex]) {
            utterance.voice = voices[selectedVoiceIndex];
        }

        // Set speed
        utterance.rate = parseFloat(speedSlider.value);

        // Event handlers
        utterance.onstart = function() {
            isPlaying = true;
            isPaused = false;
            toggleControls(true);
        };

        utterance.onend = function() {
            isPlaying = false;
            isPaused = false;
            toggleControls(false);
            progressFill.style.width = '100%';
        };

        utterance.onerror = function(event) {
            console.error('Speech synthesis error:', event);
            isPlaying = false;
            isPaused = false;
            toggleControls(false);
        };

        utterance.onboundary = function(event) {
            // Update progress bar based on character position
            if (event.name === 'word') {
                const progress = (event.charIndex / text.length) * 100;
                progressFill.style.width = progress + '%';
            }
        };

        // Start speaking
        synth.speak(utterance);
    }

    // Toggle control buttons state
    function toggleControls(playing) {
        playBtn.disabled = playing;
        pauseBtn.disabled = !playing;
        stopBtn.disabled = !playing;
    }

    // Voice change handler
    voiceSelect.addEventListener('change', function() {
        if (isPlaying) {
            // Restart with new voice
            synth.cancel();
            isPlaying = false;
            isPaused = false;
            toggleControls(false);
            progressFill.style.width = '0%';
        }
    });

    // Smooth scroll for navigation links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                const headerOffset = 80;
                const elementPosition = targetElement.getBoundingClientRect().top;
                const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });

    // Mobile menu toggle
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const navMenu = document.querySelector('.nav-menu');
    const navActions = document.querySelector('.nav-actions');

    if (mobileMenuToggle) {
        mobileMenuToggle.addEventListener('click', function() {
            // Toggle mobile menu visibility
            const isMenuVisible = navMenu.style.display === 'flex';
            
            if (isMenuVisible) {
                navMenu.style.display = 'none';
                navActions.style.display = 'none';
            } else {
                navMenu.style.display = 'flex';
                navMenu.style.flexDirection = 'column';
                navMenu.style.position = 'absolute';
                navMenu.style.top = '100%';
                navMenu.style.left = '0';
                navMenu.style.right = '0';
                navMenu.style.background = 'white';
                navMenu.style.padding = '1rem';
                navMenu.style.boxShadow = '0 4px 6px rgba(0,0,0,0.1)';
                
                navActions.style.display = 'flex';
                navActions.style.flexDirection = 'column';
                navActions.style.position = 'absolute';
                navActions.style.top = '100%';
                navActions.style.right = '0';
                navActions.style.background = 'white';
                navActions.style.padding = '1rem';
                navActions.style.gap = '0.5rem';
            }
        });
    }

    // Add some sample text to the textarea for demo purposes
    const sampleText = `Welcome to NaturalReader! This is a demonstration of our text to speech technology. 

NaturalReader is your go-to online text to speech tool that converts any text into natural-sounding speech. You can listen to documents, webpages, and more with lifelike AI voices.

Features include:
- Support for multiple file formats including PDF, DOCX, and TXT
- Over 200 premium AI voices in 50+ languages
- Adjustable playback speed from 0.5x to 2x
- Cloud storage to access your documents from any device
- Mobile apps for iOS and Android

Try it out by pasting your own text above or using this sample text. Select a voice from the dropdown menu, adjust the speed if needed, and click the play button to start listening!`;

    textInput.value = sampleText;

    // Add keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Space bar to play/pause when focused on the app
        if (e.code === 'Space' && document.activeElement !== textInput) {
            e.preventDefault();
            if (isPlaying && !isPaused) {
                pauseBtn.click();
            } else {
                playBtn.click();
            }
        }
    });

    console.log('NaturalReader Clone initialized successfully!');
});
