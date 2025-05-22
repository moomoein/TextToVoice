// Text-to-Speech Converter Script
// Created by MoMoein and Grok (xAI) - March 2025

/**
 * @class StepManager
 * @description Manages the navigation and display of different steps in the UI.
 */
class StepManager {
    /**
     * @constructor
     * @description Initializes step management, sets the current step, and attaches event listeners.
     */
    constructor() {
        this.currentStep = 1;
        this.steps = document.querySelectorAll('.step');
        this.initEventListeners();
    }

    /**
     * @method initEventListeners
     * @description Attaches click event listeners to all buttons with the '.btn' class.
     */
    initEventListeners() {
        document.querySelectorAll('.btn').forEach(button => {
            button.addEventListener('click', () => this.handleButtonClick(button));
        });
    }

    /**
     * @method handleButtonClick
     * @description Handles actions based on the clicked button's 'data-action' attribute.
     * @param {HTMLElement} button - The button element that was clicked.
     */
    handleButtonClick(button) {
        const action = button.dataset.action;
        // Perform action based on button type (next, prev, generate, reset)
        if (action === 'next') this.nextStep(button.dataset.next);
        else if (action === 'prev') this.prevStep(button.dataset.prev);
        else if (action === 'generate') this.generateAudio(); // This method is in AudioHandler
        else if (action === 'reset') this.resetForm();
    }

    /**
     * @method showStep
     * @description Hides all steps and displays the specified step.
     * @param {string} stepId - The 'data-id' of the step to display (e.g., 'step-1').
     */
    showStep(stepId) {
        // Hide all steps
        this.steps.forEach(step => step.classList.remove('active'));
        // Show the target step
        const nextStepElement = document.querySelector(`.step[data-id="${stepId}"]`);
        if (nextStepElement) {
            nextStepElement.classList.add('active');
        }
        // Update current step number, parsing from 'step-X' format
        this.currentStep = parseInt(stepId.split('-')[1]);
    }

    /**
     * @method nextStep
     * @description Validates input for the first step and then navigates to the next step.
     * @param {string} nextId - The 'data-id' of the next step.
     */
    nextStep(nextId) {
        // Validate text input only if currently on the first step
        if (this.currentStep === 1 && !document.querySelector('.text-input').value) {
            alert('لطفاً متن را وارد کنید'); // Please enter the text
            return;
        }
        this.showStep(nextId);
    }

    /**
     * @method prevStep
     * @description Navigates to the previous step.
     * @param {string} prevId - The 'data-id' of the previous step.
     */
    prevStep(prevId) {
        this.showStep(prevId);
    }

    /**
     * @method resetFormInputs
     * @description Resets the text input and radio button selections to their default values.
     */
    resetFormInputs() {
        document.querySelector('.text-input').value = '';
        // Set default voice and vibe
        const defaultVoice = document.querySelector('input[name="voice"][value="Fable"]');
        if (defaultVoice) defaultVoice.checked = true;
        const defaultVibe = document.querySelector('input[name="vibe"][value="Dramatic"]');
        if (defaultVibe) defaultVibe.checked = true;
    }

    /**
     * @method resetForm
     * @description Resets all form inputs, hides the audio player, and returns to the first step.
     */
    resetForm() {
        this.resetFormInputs();
        const audioPlayer = document.querySelector('.audio-player');
        if (audioPlayer) audioPlayer.classList.remove('active');
        this.showStep('step-1'); // Go back to the first step
    }
}

/**
 * @class AudioHandler
 * @extends StepManager
 * @description Handles audio generation functionality, including API interaction and UI updates for audio playback.
 */
class AudioHandler extends StepManager {
    /**
     * @method generateAudio
     * @async
     * @description Handles the audio generation process. It collects user input,
     * sends a request to the backend API, and manages UI updates for loading and audio playback.
     */
    async generateAudio() {
        const text = document.querySelector('.text-input').value;
        const voiceInput = document.querySelector('input[name="voice"]:checked');
        const vibeInput = document.querySelector('input[name="vibe"]:checked');

        // Ensure voice and vibe inputs are selected, otherwise default or handle error
        const voice = voiceInput ? voiceInput.value : 'Fable'; // Default to 'Fable' if none selected
        const vibe = vibeInput ? vibeInput.value : 'Dramatic'; // Default to 'Dramatic' if none selected
        if (!text) {
            alert('لطفاً متن را وارد کنید'); // Please enter the text
            return;
        }

        this.showStep('step-4'); // Navigate to the results/spinner step
        const spinner = document.querySelector('.spinner');
        const audioPlayer = document.querySelector('.audio-player');
        const audioElement = document.querySelector('.audio-element');

        // Ensure elements exist before manipulating them
        if (spinner) spinner.style.display = 'block';
        if (audioPlayer) audioPlayer.classList.remove('active');
        if (audioElement) audioElement.src = ''; // Clear previous audio

        try {
            // API call to the backend to generate audio
            const response = await fetch('', { // URL is empty, assumes current page or a base URL is set elsewhere
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ text, voice, vibe })
            });

            const data = await response.json();

            if (data.success && data.audio_data) {
                if (audioElement) audioElement.src = data.audio_data;
                if (audioPlayer) audioPlayer.classList.add('active'); // Show audio player
            } else {
                // Handle generation error
                alert('متاسفانه در تولید صدا خطایی رخ داد. جزئیات: ' + (data.error || 'خطای نامشخص'));
            }
        } catch (error) {
            // Handle network or other unexpected errors
            alert('خطا در برقراری ارتباط با سرور. لطفاً اتصال اینترنت خود را بررسی کنید یا بعداً دوباره تلاش کنید. جزئیات: ' + error.message);
        } finally {
            // Hide spinner regardless of outcome
            if (spinner) spinner.style.display = 'none';
        }
    }
}

// Initialize the application once the DOM is fully loaded
document.addEventListener('DOMContentLoaded', () => {
    const audioHandler = new AudioHandler();
    audioHandler.showStep('step-1'); // Start the application at the first step
});