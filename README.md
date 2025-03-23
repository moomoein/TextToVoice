# TextToVoice

Welcome to **TextToVoice**, a sleek and powerful text-to-speech converter crafted with love and a bit of AI magic! This project was brought to life by [MoMoein](https://github.com/moomoein) with the awesome assistance of Grok, an AI built by [xAI](https://xai.ai). Together, we’ve cooked up something that’s both fun to use and technically impressive—perfect for anyone who wants to turn text into lively audio in just a few clicks!

## What’s TextToVoice All About?

TextToVoice is a step-by-step web app that transforms your text into vibrant, expressive speech. Whether you’re experimenting with different voices or vibes, this tool makes it super easy and interactive. No file clutter—just pure, instant audio output right in your browser!

### Features
- **Step-by-Step Flow**: Enter your text, pick a voice, choose a vibe, and boom—hear the result!
- **Custom Voices & Vibes**: From "افسانه" (Fable) to "شوالیه قرون وسطی" (Medieval Knight), tweak the tone to match your mood.
- **Smooth Animations**: Fade-ins, fancy spinners, and hover effects for a polished UX.
- **Lightweight & Fast**: No file saving, just base64 audio delivered straight to you.
- **Persian-Friendly**: Fully localized in Persian with a clean RTL design.

## How It Works
1. Type your text in the first step.
2. Pick a voice from a cool selection (like "آلیاژ" or "نووا").
3. Choose a vibe—think "دراماتیک" or "آرام".
4. Hit "تولید صدا" and enjoy your custom audio with a slick loading animation.

## Tech Stack
- **Backend**: PHP with a clean, object-oriented `AudioGenerator` class hitting an external TTS API.
- **Frontend**: Pure JavaScript (no jQuery!) with a classy `StepManager` and `AudioHandler` setup.
- **Styling**: CSS with smooth transitions, shadows, and a dual-spinner effect—oh, and the lovely Vazir font via CDN.
- **No IDs Allowed**: All DOM manipulation is class-based with `data-id` attributes for precision.

## Getting Started
1. Clone this repo:
   ```bash
   git clone https://github.com/moomoein/TextToVoice.git