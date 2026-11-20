# Fuzzygueño Website

A beautiful, pastel-themed website for Fuzzygueño - a handmade business specializing in fuzzywire flowers, bouquets, and custom keychains.

## 🚀 Getting Started

Simply open `index.html` in your web browser to view the website. No build process required!

## 📁 Project Structure

```
Fuzzygueño/
├── index.html          # Main HTML file
├── styles.css          # All styling (pastel theme)
├── script.js           # Interactive features (form, FAQ, smooth scroll)
├── images/             # Image folder (add your photos here)
│   └── README.md       # Image requirements guide
└── README.md           # This file
```

## 🖼️ Adding Images

1. Place your logo in `images/` and name it `logo-placeholder.png`
2. Add product photos with these names:
   - `product-single-stem-placeholder.png`
   - `product-standard-bouquet-placeholder.png`
   - `product-big-bouquet-placeholder.png`
   - `product-keychains-placeholder.png`

See `images/README.md` for more details.

## ✏️ Easy Editing Guide

### Changing Colors
Edit the color variables in `styles.css` at the top (lines 10-17):
```css
:root {
    --pink: #FFB6D9;
    --lavender: #D4B5FF;
    --mint: #B5FFD4;
    /* ... */
}
```

### Updating Prices
Edit the prices in `index.html` in the product cards section (around line 60-120).

### Changing Contact Links
Update the links in the Contact section (around line 200):
- Facebook: `https://facebook.com/fuzzygueno`
- Instagram: `https://instagram.com/fuzzygueno`
- Email: `mailto:info@fuzzygueno.com`
- Messenger: `https://m.me/fuzzygueno`

### Modifying Form Submission
Edit `script.js` around line 50 to change how the form submits (Messenger, Email, etc.)

### Adding/Removing FAQ Items
Copy a `.faq-item` block in `index.html` and modify the question/answer.

## 📱 Features

- ✅ Fully responsive (mobile-friendly)
- ✅ Smooth scrolling navigation
- ✅ Interactive FAQ accordion
- ✅ Order form with validation
- ✅ Pastel color scheme
- ✅ Soft shadows and rounded corners
- ✅ Decorative icons and animations

## 🎨 Design Notes

- Pastel color palette: pink, lavender, mint, cream
- Rounded shapes throughout
- Soft shadows for depth
- Smooth animations and transitions
- Cute, cozy handmade vibe

## 📝 Notes

- The form currently opens Messenger when submitted. You can change this in `script.js`.
- All sections are clearly labeled with HTML comments.
- The site is ready to use - just add your images!

---

Made with 💖 for Fuzzygueño

