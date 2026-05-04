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

    // ========================================
    // FUTURE ENHANCEMENTS
    // ========================================

    // Dark Mode Toggle
    const themeToggle = document.getElementById('theme-toggle');
    const body = document.body;
    
    // Check for saved theme preference
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark') {
        body.classList.add('dark-mode');
        themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
    }

    themeToggle.addEventListener('click', () => {
        body.classList.toggle('dark-mode');
        
        if (body.classList.contains('dark-mode')) {
            themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
            localStorage.setItem('theme', 'dark');
            showToast('Dark mode enabled', 'info');
        } else {
            themeToggle.innerHTML = '<i class="fas fa-moon"></i>';
            localStorage.setItem('theme', 'light');
            showToast('Light mode enabled', 'info');
        }
    });

    // Modal Management
    const modals = {
        upload: document.getElementById('upload-modal'),
        history: document.getElementById('history-modal'),
        bookmarks: document.getElementById('bookmarks-modal'),
        settings: document.getElementById('settings-modal')
    };

    const modalButtons = {
        upload: document.getElementById('upload-btn'),
        history: document.getElementById('history-btn'),
        bookmark: document.getElementById('bookmark-btn'),
        settings: document.getElementById('settings-btn')
    };

    // Open modals
    Object.keys(modalButtons).forEach(key => {
        if (modalButtons[key]) {
            modalButtons[key].addEventListener('click', () => {
                const modalName = key === 'bookmark' ? 'bookmarks' : key;
                openModal(modals[modalName]);
            });
        }
    });

    // Close modals
    document.querySelectorAll('.modal-close').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const modal = e.target.closest('.modal');
            closeModal(modal);
        });
    });

    // Close modal on outside click
    window.addEventListener('click', (e) => {
        if (e.target.classList.contains('modal')) {
            closeModal(e.target);
        }
    });

    function openModal(modal) {
        if (modal) {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeModal(modal) {
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    // File Upload Functionality
    const uploadArea = document.getElementById('upload-area');
    const fileInput = document.getElementById('file-input');
    const fileList = document.getElementById('file-list');
    const confirmUploadBtn = document.getElementById('confirm-upload');
    const cancelUploadBtn = document.getElementById('cancel-upload');
    
    let selectedFiles = [];

    if (uploadArea) {
        uploadArea.addEventListener('click', () => fileInput.click());
        
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            handleFiles(e.dataTransfer.files);
        });

        fileInput.addEventListener('change', (e) => {
            handleFiles(e.target.files);
        });
    }

    function handleFiles(files) {
        const validTypes = ['.pdf', '.docx', '.txt', '.rtf', '.epub'];
        
        Array.from(files).forEach(file => {
            const ext = '.' + file.name.split('.').pop().toLowerCase();
            if (validTypes.includes(ext)) {
                selectedFiles.push(file);
            } else {
                showToast(`Unsupported file type: ${ext}`, 'error');
            }
        });

        updateFileList();
    }

    function updateFileList() {
        if (!fileList) return;

        if (selectedFiles.length === 0) {
            fileList.innerHTML = '';
            if (confirmUploadBtn) confirmUploadBtn.disabled = true;
            return;
        }

        fileList.innerHTML = selectedFiles.map((file, index) => `
            <div class="file-item">
                <div class="file-info">
                    <i class="fas fa-file-alt"></i>
                    <span>${file.name}</span>
                    <span style="color: #999; font-size: 0.85rem;">(${formatFileSize(file.size)})</span>
                </div>
                <button class="remove-file" data-index="${index}">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `).join('');

        // Add remove event listeners
        fileList.querySelectorAll('.remove-file').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const index = parseInt(e.target.closest('button').dataset.index);
                selectedFiles.splice(index, 1);
                updateFileList();
            });
        });

        if (confirmUploadBtn) confirmUploadBtn.disabled = false;
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }

    if (confirmUploadBtn) {
        confirmUploadBtn.addEventListener('click', () => {
            if (selectedFiles.length > 0) {
                // Simulate file upload and text extraction
                showToast(`Uploading ${selectedFiles.length} file(s)...`, 'info');
                
                setTimeout(() => {
                    // For demo purposes, just add sample text
                    const sampleTexts = [
                        "The quick brown fox jumps over the lazy dog. This is a sample text extracted from your uploaded document.",
                        "NaturalReader makes it easy to listen to your documents. Simply upload any file and choose your preferred voice.",
                        "Reading has never been easier. With our advanced text-to-speech technology, you can listen to any document anywhere."
                    ];
                    
                    const randomText = sampleTexts[Math.floor(Math.random() * sampleTexts.length)];
                    textInput.value = textInput.value ? textInput.value + '\n\n' + randomText : randomText;
                    
                    addToHistory('Uploaded Document', new Date());
                    closeModal(modals.upload);
                    showToast('File(s) uploaded successfully!', 'success');
                    
                    // Reset
                    selectedFiles = [];
                    fileInput.value = '';
                    updateFileList();
                }, 1500);
            }
        });
    }

    if (cancelUploadBtn) {
        cancelUploadBtn.addEventListener('click', () => {
            closeModal(modals.upload);
            selectedFiles = [];
            updateFileList();
        });
    }

    // History Management
    let readingHistory = JSON.parse(localStorage.getItem('readingHistory') || '[]');

    function addToHistory(title, timestamp) {
        readingHistory.unshift({ title, timestamp: timestamp.toISOString() });
        if (readingHistory.length > 50) readingHistory.pop();
        localStorage.setItem('readingHistory', JSON.stringify(readingHistory));
        updateHistoryDisplay();
    }

    function updateHistoryDisplay() {
        const historyList = document.getElementById('history-list');
        if (!historyList) return;

        if (readingHistory.length === 0) {
            historyList.innerHTML = '<p class="empty-state">No history yet. Start reading to see your history here.</p>';
            return;
        }

        historyList.innerHTML = readingHistory.map((item, index) => `
            <div class="history-item" data-index="${index}">
                <strong>${item.title}</strong><br>
                <small style="color: #999;">${new Date(item.timestamp).toLocaleString()}</small>
            </div>
        `).join('');

        // Add click handlers
        historyList.querySelectorAll('.history-item').forEach(item => {
            item.addEventListener('click', () => {
                const index = parseInt(item.dataset.index);
                const historyItem = readingHistory[index];
                textInput.value = historyItem.title;
                closeModal(modals.history);
            });
        });
    }

    const clearHistoryBtn = document.getElementById('clear-history');
    if (clearHistoryBtn) {
        clearHistoryBtn.addEventListener('click', () => {
            if (confirm('Are you sure you want to clear your reading history?')) {
                readingHistory = [];
                localStorage.removeItem('readingHistory');
                updateHistoryDisplay();
                showToast('History cleared', 'success');
            }
        });
    }

    // Initialize history display
    updateHistoryDisplay();

    // Bookmark Management
    let bookmarks = JSON.parse(localStorage.getItem('bookmarks') || '[]');

    function addBookmark() {
        const currentPosition = speechSynthesis.getSpeaking() ? 
            Math.floor(currentChar / textInput.value.length * 100) : 0;
        
        const bookmark = {
            id: Date.now(),
            position: currentPosition,
            text: textInput.value.substring(0, 100) + '...',
            timestamp: new Date().toISOString()
        };

        bookmarks.unshift(bookmark);
        if (bookmarks.length > 20) bookmarks.pop();
        localStorage.setItem('bookmarks', JSON.stringify(bookmarks));
        updateBookmarksDisplay();
        showToast('Bookmark added', 'success');
    }

    function updateBookmarksDisplay() {
        const bookmarksList = document.getElementById('bookmarks-list');
        if (!bookmarksList) return;

        if (bookmarks.length === 0) {
            bookmarksList.innerHTML = '<p class="empty-state">No bookmarks yet. Add bookmarks to save your favorite positions.</p>';
            return;
        }

        bookmarksList.innerHTML = bookmarks.map((bookmark, index) => `
            <div class="bookmark-item" data-index="${index}">
                <strong>Position: ${bookmark.position}%</strong><br>
                <small style="color: #666;">${bookmark.text}</small><br>
                <small style="color: #999;">${new Date(bookmark.timestamp).toLocaleString()}</small>
            </div>
        `).join('');

        // Add click handlers
        bookmarksList.querySelectorAll('.bookmark-item').forEach(item => {
            item.addEventListener('click', () => {
                const index = parseInt(item.dataset.index);
                const bookmark = bookmarks[index];
                const targetPosition = Math.floor(bookmark.position / 100 * textInput.value.length);
                currentChar = targetPosition;
                closeModal(modals.bookmarks);
                showToast('Jumped to bookmark', 'info');
            });
        });
    }

    const bookmarkBtn = document.getElementById('bookmark-btn');
    if (bookmarkBtn) {
        bookmarkBtn.addEventListener('click', addBookmark);
    }

    const clearBookmarksBtn = document.getElementById('clear-bookmarks');
    if (clearBookmarksBtn) {
        clearBookmarksBtn.addEventListener('click', () => {
            if (confirm('Are you sure you want to clear all bookmarks?')) {
                bookmarks = [];
                localStorage.removeItem('bookmarks');
                updateBookmarksDisplay();
                showToast('Bookmarks cleared', 'success');
            }
        });
    }

    // Initialize bookmarks display
    updateBookmarksDisplay();

    // Settings Management
    const settings = {
        autoPlay: false,
        highlightText: true,
        keyboardShortcuts: true,
        fontSize: 16,
        lineHeight: 1.6,
        pitch: 1
    };

    // Load saved settings
    const savedSettings = JSON.parse(localStorage.getItem('settings') || '{}');
    Object.assign(settings, savedSettings);

    // Apply settings
    function applySettings() {
        textInput.style.fontSize = settings.fontSize + 'px';
        textInput.style.lineHeight = settings.lineHeight;
        
        // Update UI controls
        const fontSizeSlider = document.getElementById('font-size');
        const fontSizeValue = document.getElementById('font-size-value');
        const lineHeightSelect = document.getElementById('line-height');
        const pitchSlider = document.getElementById('pitch');
        const pitchValue = document.getElementById('pitch-value');
        const autoPlayCheckbox = document.getElementById('auto-play');
        const highlightTextCheckbox = document.getElementById('highlight-text');
        const keyboardShortcutsCheckbox = document.getElementById('keyboard-shortcuts');

        if (fontSizeSlider) fontSizeSlider.value = settings.fontSize;
        if (fontSizeValue) fontSizeValue.textContent = settings.fontSize + 'px';
        if (lineHeightSelect) lineHeightSelect.value = settings.lineHeight;
        if (pitchSlider) pitchSlider.value = settings.pitch;
        if (pitchValue) pitchValue.textContent = settings.pitch.toFixed(1);
        if (autoPlayCheckbox) autoPlayCheckbox.checked = settings.autoPlay;
        if (highlightTextCheckbox) highlightTextCheckbox.checked = settings.highlightText;
        if (keyboardShortcutsCheckbox) keyboardShortcutsCheckbox.checked = settings.keyboardShortcuts;
    }

    // Settings event listeners
    const fontSizeSlider = document.getElementById('font-size');
    if (fontSizeSlider) {
        fontSizeSlider.addEventListener('input', (e) => {
            settings.fontSize = parseInt(e.target.value);
            document.getElementById('font-size-value').textContent = settings.fontSize + 'px';
            textInput.style.fontSize = settings.fontSize + 'px';
            saveSettings();
        });
    }

    const lineHeightSelect = document.getElementById('line-height');
    if (lineHeightSelect) {
        lineHeightSelect.addEventListener('change', (e) => {
            settings.lineHeight = parseFloat(e.target.value);
            textInput.style.lineHeight = settings.lineHeight;
            saveSettings();
        });
    }

    const pitchSlider = document.getElementById('pitch');
    if (pitchSlider) {
        pitchSlider.addEventListener('input', (e) => {
            settings.pitch = parseFloat(e.target.value);
            document.getElementById('pitch-value').textContent = settings.pitch.toFixed(1);
            if (utterance) utterance.pitch = settings.pitch;
            saveSettings();
        });
    }

    const autoPlayCheckbox = document.getElementById('auto-play');
    if (autoPlayCheckbox) {
        autoPlayCheckbox.addEventListener('change', (e) => {
            settings.autoPlay = e.target.checked;
            saveSettings();
        });
    }

    const highlightTextCheckbox = document.getElementById('highlight-text');
    if (highlightTextCheckbox) {
        highlightTextCheckbox.addEventListener('change', (e) => {
            settings.highlightText = e.target.checked;
            saveSettings();
        });
    }

    const keyboardShortcutsCheckbox = document.getElementById('keyboard-shortcuts');
    if (keyboardShortcutsCheckbox) {
        keyboardShortcutsCheckbox.addEventListener('change', (e) => {
            settings.keyboardShortcuts = e.target.checked;
            saveSettings();
        });
    }

    function saveSettings() {
        localStorage.setItem('settings', JSON.stringify(settings));
    }

    // Apply settings on load
    applySettings();

    // Download Audio (Simulation)
    const downloadBtn = document.getElementById('download-btn');
    if (downloadBtn) {
        downloadBtn.addEventListener('click', () => {
            if (!textInput.value.trim()) {
                showToast('Please enter some text first', 'error');
                return;
            }
            
            showToast('Preparing audio download...', 'info');
            
            // Simulate audio generation
            setTimeout(() => {
                // Create a simple text file as a demo
                const blob = new Blob([textInput.value], { type: 'text/plain' });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'naturalreader-text.txt';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
                
                showToast('Download started! (Note: Full audio export requires premium)', 'success');
            }, 1000);
        });
    }

    // Share Functionality
    const shareBtn = document.getElementById('share-btn');
    if (shareBtn) {
        shareBtn.addEventListener('click', async () => {
            if (!textInput.value.trim()) {
                showToast('Please enter some text first', 'error');
                return;
            }

            if (navigator.share) {
                try {
                    await navigator.share({
                        title: 'NaturalReader Text',
                        text: textInput.value.substring(0, 500),
                        url: window.location.href
                    });
                    showToast('Shared successfully!', 'success');
                } catch (err) {
                    if (err.name !== 'AbortError') {
                        showToast('Share failed', 'error');
                    }
                }
            } else {
                // Fallback: copy to clipboard
                textInput.select();
                document.execCommand('copy');
                showToast('Text copied to clipboard!', 'success');
            }
        });
    }

    // Word Count Display
    function updateWordCount() {
        const text = textInput.value.trim();
        const wordCount = text ? text.split(/\s+/).length : 0;
        const charCount = text.length;
        
        let wordCountEl = document.querySelector('.word-count');
        if (!wordCountEl) {
            wordCountEl = document.createElement('div');
            wordCountEl.className = 'word-count';
            textInput.parentElement.appendChild(wordCountEl);
        }
        
        wordCountEl.textContent = `${wordCount} words | ${charCount} characters`;
    }

    textInput.addEventListener('input', updateWordCount);
    updateWordCount();

    // Toast Notification System
    function showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerHTML = `
            <span class="toast-message">${message}</span>
            <button class="toast-close">&times;</button>
        `;
        
        document.body.appendChild(toast);
        
        toast.querySelector('.toast-close').addEventListener('click', () => {
            toast.remove();
        });
        
        setTimeout(() => {
            if (toast.parentElement) {
                toast.style.animation = 'slideInRight 0.3s ease reverse';
                setTimeout(() => toast.remove(), 300);
            }
        }, 3000);
    }

    // Time Display
    function updateTimeDisplay() {
        if (!utterance || !textInput.value) return;
        
        const totalTime = Math.ceil(textInput.value.length / 15); // Rough estimate
        const elapsed = Math.ceil(currentChar / 15);
        const remaining = totalTime - elapsed;
        
        let timeDisplayEl = document.querySelector('.time-display');
        if (!timeDisplayEl) {
            timeDisplayEl = document.createElement('div');
            timeDisplayEl.className = 'time-display';
            progressBar.parentElement.appendChild(timeDisplayEl);
        }
        
        const formatTime = (seconds) => {
            const mins = Math.floor(seconds / 60);
            const secs = seconds % 60;
            return `${mins}:${secs.toString().padStart(2, '0')}`;
        };
        
        timeDisplayEl.textContent = `${formatTime(elapsed)} / ${formatTime(totalTime)} (Remaining: ${formatTime(remaining)})`;
    }

    console.log('NaturalReader Clone with Future Enhancements initialized successfully!');
});
