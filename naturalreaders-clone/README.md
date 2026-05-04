# NaturalReader Clone

A responsive, functional clone of the NaturalReader online text-to-speech website. This project recreates the core features and design of NaturalReader's online TTS platform.

## Features

### Implemented Features

- **Responsive Design**: Fully responsive layout that works on desktop, tablet, and mobile devices
- **Text-to-Speech Functionality**: 
  - Play, pause, and stop controls
  - Speed adjustment (0.5x to 2x)
  - Voice selection from available system voices
  - Progress bar tracking
  - Keyboard shortcuts (Space bar to play/pause)
- **Modern UI Components**:
  - Fixed navigation header with smooth scrolling
  - Hero section with interactive app preview
  - Features grid showcasing key capabilities
  - How It Works section with step-by-step guide
  - Use Cases section for different user types
  - Call-to-action section
  - Comprehensive footer with links
- **Interactive Elements**:
  - Mobile menu toggle
  - Smooth scroll navigation
  - Hover effects and transitions
  - Sample text pre-loaded for demonstration

## Project Structure

```
naturalreaders-clone/
├── index.html          # Main HTML file
├── css/
│   └── styles.css      # All styling and responsive design
├── js/
│   └── app.js          # Text-to-speech functionality and interactions
└── assets/             # Reserved for images and other assets
```

## Technologies Used

- **HTML5**: Semantic markup with accessibility features
- **CSS3**: Modern CSS with custom properties (CSS variables), Grid, Flexbox
- **JavaScript**: Web Speech API (SpeechSynthesis) for text-to-speech
- **Google Fonts**: Inter font family for modern typography

## Browser Support

The text-to-speech functionality uses the Web Speech API, which is supported in:
- Chrome/Edge (best support)
- Safari
- Firefox (limited voice options)

For best results, use Chrome or Edge browsers.

## Getting Started

### Option 1: Open Directly

Simply open `index.html` in your web browser:

```bash
# On macOS
open index.html

# On Windows
start index.html

# On Linux
xdg-open index.html
```

### Option 2: Use a Local Server

For the best experience, serve the files using a local web server:

```bash
# Using Python 3
python3 -m http.server 8000

# Then open http://localhost:8000 in your browser
```

Or with Node.js:
```bash
npx serve .
```

## Usage Instructions

1. **Enter or Paste Text**: Type or paste any text into the textarea in the hero section
2. **Select a Voice**: Choose from available voices in the dropdown menu
3. **Adjust Speed**: Use the slider to set playback speed (0.5x - 2x)
4. **Control Playback**:
   - Click the **Play** button (▶) to start reading
   - Click the **Pause** button (⏸) to pause
   - Click the **Stop** button (⏹) to stop completely
   - Press **Space bar** to toggle play/pause

## Key Features Explained

### Text-to-Speech Engine

The application uses the browser's built-in Web Speech API (`window.speechSynthesis`) which:
- Requires no backend server
- Works entirely client-side
- Uses system-installed voices
- Supports multiple languages (depending on OS)

### Responsive Design

The site adapts to different screen sizes:
- **Desktop (>992px)**: Full two-column layout with all features visible
- **Tablet (768px-992px)**: Stacked hero section, adjusted footer
- **Mobile (<768px)**: Hamburger menu, single-column layouts, touch-friendly controls

### Color Scheme

- Primary: Indigo (#4F46E5)
- Secondary: Emerald (#10B981)
- Text colors optimized for readability
- Gradient backgrounds for visual interest

## Customization

### Changing Colors

Edit the CSS custom properties in `css/styles.css`:

```css
:root {
    --primary-color: #4F46E5;      /* Main brand color */
    --primary-dark: #4338CA;       /* Hover states */
    --secondary-color: #10B981;    /* Accent color */
    /* ... more variables */
}
```

### Adding More Voices

Voices are automatically populated from the system. To add premium voices, you would need to integrate with a TTS service API like:
- Google Cloud Text-to-Speech
- Amazon Polly
- Microsoft Azure Cognitive Services
- NaturalReader API

## Limitations

This is a frontend clone with the following limitations compared to the original NaturalReader:

1. **Voice Quality**: Uses system voices instead of premium AI voices
2. **File Upload**: No document upload functionality (PDF, DOCX, etc.)
3. **Cloud Storage**: No user accounts or cloud storage
4. **Audio Download**: Cannot download generated audio files
5. **Advanced Features**: No voice cloning, SSML support, or pronunciation editor

## Future Enhancements ✨

### Recently Added Features

- [x] **Dark Mode Toggle** - Switch between light and dark themes with persistent preference
- [x] **File Upload Modal** - Drag & drop interface for PDF, DOCX, TXT, RTF, EPUB files
- [x] **Reading History** - Track your reading sessions with localStorage persistence
- [x] **Bookmarks System** - Save and jump to specific positions in documents
- [x] **Settings Panel** - Customize font size, line height, pitch, and playback preferences
- [x] **Enhanced Controls Bar** - Floating action buttons for quick access to features
- [x] **Word Count Display** - Real-time word and character counting
- [x] **Time Estimation** - Estimated reading time and remaining time display
- [x] **Share Functionality** - Native share API with clipboard fallback
- [x] **Download Feature** - Export text files (audio export simulation)
- [x] **Toast Notifications** - Beautiful notification system for user feedback
- [x] **Accessibility Improvements** - Reduced motion support, high contrast mode, print styles

### Planned Enhancements

- [ ] File upload parsing (actual PDF/DOCX text extraction)
- [ ] User authentication and cloud storage
- [ ] Integration with premium TTS APIs (Google, Amazon, Azure)
- [ ] Real audio file download (MP3/WAV export)
- [ ] Playlist management for multiple documents
- [ ] Text highlighting while reading
- [ ] Multiple language support with auto-detection
- [ ] Pronunciation dictionary and custom phonemes
- [ ] SSML support for advanced speech control
- [ ] Voice cloning integration
- [ ] Background playback support
- [ ] PWA (Progressive Web App) capabilities
- [ ] Offline support with service workers
- [ ] Keyboard shortcuts customization
- [ ] Reading statistics and analytics

## License

This is an educational clone created for demonstration purposes. NaturalReader is a trademark of its respective owners. This project is not affiliated with or endorsed by NaturalReader.

## Credits

- Design inspired by NaturalReader (naturalreaders.com)
- Font: Inter by Rasmus Andersson
- Icons: Custom SVG icons
- Built with vanilla HTML, CSS, and JavaScript

---

**Note**: This is a simplified clone for educational purposes. For production text-to-speech needs, consider using the official NaturalReader service or other professional TTS solutions.
