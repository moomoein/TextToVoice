// Text-to-Speech Converter Script
// Created by MoMoein and Grok (xAI) - March 2025

class StepManager {
    constructor() {
        this.currentStep = 1;
        this.steps = document.querySelectorAll('.step');
        this.initEventListeners();
    }

    initEventListeners() {
        document.querySelectorAll('.btn').forEach(button => {
            button.addEventListener('click', () => this.handleButtonClick(button));
        });
    }

    handleButtonClick(button) {
        const action = button.dataset.action;
        if (action === 'next') this.nextStep(button.dataset.next);
        else if (action === 'prev') this.prevStep(button.dataset.prev);
        else if (action === 'generate') this.generateAudio();
        else if (action === 'reset') this.resetForm();
    }

    showStep(stepId) {
        this.steps.forEach(step => step.classList.remove('active'));
        const nextStep = document.querySelector(`.step[data-id="${stepId}"]`);
        nextStep.classList.add('active');
        this.currentStep = parseInt(stepId.split('-')[1]);
    }

    nextStep(nextId) {
        if (this.currentStep === 1 && !document.querySelector('.text-input').value) {
            alert('لطفاً متن را وارد کنید');
            return;
        }
        this.showStep(nextId);
    }

    prevStep(prevId) {
        this.showStep(prevId);
    }

    resetFormInputs() {
        document.querySelector('.text-input').value = '';
        document.querySelector('input[name="voice"][value="Fable"]').checked = true;
        document.querySelector('input[name="vibe"][value="Dramatic"]').checked = true;
    }

    resetForm() {
        this.resetFormInputs();
        document.querySelector('.audio-player').classList.remove('active');
        this.showStep('step-1');
    }
}

class AudioHandler extends StepManager {
    async generateAudio() {
        const text = document.querySelector('.text-input').value;
        const voice = document.querySelector('input[name="voice"]:checked').value;
        const vibe = document.querySelector('input[name="vibe"]:checked').value;

        if (!text) {
            alert('لطفاً متن را وارد کنید');
            return;
        }

        this.showStep('step-4');
        const spinner = document.querySelector('.spinner');
        const audioPlayer = document.querySelector('.audio-player');
        const audioElement = document.querySelector('.audio-element');

        spinner.style.display = 'block';
        audioPlayer.classList.remove('active');

        try {
            const response = await fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ text, voice, vibe })
            });

            const data = await response.json();

            if (data.success) {
                audioElement.src = data.audio_data;
                audioPlayer.classList.add('active');
            } else {
                alert('خطایی در تولید صدا رخ داد: ' + data.error);
            }
        } catch (error) {
            alert('خطایی در ارتباط با سرور رخ داد: ' + error.message);
        } finally {
            spinner.style.display = 'none';
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const audioHandler = new AudioHandler();
    audioHandler.showStep('step-1'); // Start at step 1
});