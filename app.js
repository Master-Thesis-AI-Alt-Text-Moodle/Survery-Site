const app = {
    currentImageIndex: 0,
    totalImages: 0,
    currentModelOrder: [],

    init: function() {
        this.loadWelcomePage();
    },

    loadWelcomePage: function() {
        const content = `
            <h1 class="text-2xl font-bold text-center mb-6">Welcome to the Image-Text Evaluation Survey</h1>
            
            <div class="bg-white p-6 rounded-lg shadow-md mb-8">
                <button id="toggleResearchInfo" class="w-full text-left text-xl font-semibold mb-4 flex justify-between items-center md:cursor-default">
                    About This Research
                    <svg class="w-6 h-6 transform transition-transform duration-200 md:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div id="researchInfo" class="hidden md:block">
                    <p class="mb-4">This survey is part of a Master's Thesis research project titled:</p>
                    <p class="text-lg font-medium text-blue-600 mb-4">"AI-Generated Alternative Text Suggestions for Images in Moodle: Enhancing Web Accessibility for Visually Impaired Users"</p>
                    <p class="mb-4">Your participation will help evaluate the effectiveness of AI-generated alternative texts for images, aimed at improving web accessibility for visually impaired users in Moodle learning environments.</p>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md mb-8">
                <h2 class="text-xl font-semibold mb-4">Key Information:</h2>
                <ul class="list-disc list-inside space-y-2 mb-6">
                    <li>This survey should take approximately <span class="font-medium text-green-600">5 minutes</span> to complete.</li>
                    <li>You'll evaluate AI-generated descriptions for a series of images.</li>
                    <li>Your responses are anonymous and used for research purposes only.</li>
                </ul>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md">
                <button id="toggleInstructions" class="w-full text-left text-xl font-semibold mb-4 flex justify-between items-center md:cursor-default">
                    Instructions
                    <svg class="w-6 h-6 transform transition-transform duration-200 md:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div id="instructionsInfo" class="hidden md:block">
                    <ol class="list-decimal list-inside space-y-2 mb-6">
                        <li>You will be shown a series of images, one at a time.</li>
                        <li>For each image, you'll see 5 different AI-generated text descriptions.</li>
                        <li>Rate each description on a scale from 1 (Bad) to 5 (Excellent).</li>
                        <li>Use the slider under each description to set your rating.</li>
                        <li>Click 'Next Image' to proceed after rating all descriptions.</li>
                        <li>Complete the evaluation for all images to finish the study.</li>
                    </ol>
                </div>
            </div>

            <div class="text-center mt-8">
                <button onclick="app.startEvaluation()" class="bg-blue-500 text-white px-6 py-3 rounded-lg hover:bg-blue-600 transition duration-300 text-lg font-semibold">
                    Start Evaluation
                </button>
            </div>
        `;
        document.getElementById('app').innerHTML = content;

        // Add event listeners for toggling sections on mobile
        ['toggleResearchInfo', 'toggleInstructions'].forEach(id => {
            document.getElementById(id).addEventListener('click', function() {
                if (window.innerWidth < 768) { // Only toggle on mobile
                    const infoDiv = this.nextElementSibling;
                    const arrow = this.querySelector('svg');
                    infoDiv.classList.toggle('hidden');
                    arrow.classList.toggle('rotate-180');
                }
            });
        });

        // Initial check to show/hide sections based on screen size
        this.checkScreenSize();

        // Add resize listener to adjust visibility on screen size change
        window.addEventListener('resize', this.checkScreenSize);
    },

    checkScreenSize: function() {
        const isMobile = window.innerWidth < 768;
        ['researchInfo', 'instructionsInfo'].forEach(id => {
            const element = document.getElementById(id);
            if (element) {
                element.classList.toggle('hidden', isMobile);
            }
        });
    },
    startEvaluation: function() {
        this.loadImage(0);
    },

    loadImage: function(index) {
        axios.get(`get_image.php?id=${index}`)
            .then(response => {
                this.currentImageIndex = index;
                this.totalImages = response.data.totalImages;
                this.renderEvaluationPage(response.data);
            })
            .catch(error => {
                console.error('Error loading image:', error);
                alert('Error loading image. Please try again.');
            });
    },

    renderEvaluationPage: function(data) {
        this.currentModelOrder = Object.keys(data.modelTextPairs);
        const content = `
            <header class="mb-8">
                <h1 class="text-2xl font-semibold text-gray-800">Image Evaluation</h1>
                <p class="text-sm text-gray-600">Image ${this.currentImageIndex + 1} of ${this.totalImages}</p>
            </header>
            
            <div class="evaluation-card p-6 mb-8">
                <div class="mb-6">
                    <img src="${data.imagePath}" alt="Image ${this.currentImageIndex + 1}" class="w-full h-auto object-contain rounded-md" style="max-height: 300px;">
                </div>

                <div class="space-y-6">
                    ${Object.entries(data.modelTextPairs).map(([model, text], index) => `
                        <div class="bg-gray-50 p-4 rounded-md">
                            <p class="text-sm font-medium text-gray-700 mb-2">${text}</p>
                            <input type="range" min="1" max="5" value="3" class="slider" id="slider-${index}">
                            <div class="flex justify-between text-xs text-gray-500 mt-1">
                            <span>Not accurate</span>
                            <span>Average</span>
                            <span>Very accurate</span>
                            </div>
                        </div>
                    `).join('')}
                </div>

                <div class="mt-8 flex justify-center">
                    ${this.currentImageIndex < this.totalImages - 1 ? `
                        <button onclick="app.submitRatings()" class="bg-blue-500 text-white px-6 py-2 rounded-md text-lg hover:bg-blue-600 transition duration-300">Next Image</button>
                    ` : `
                        <button onclick="app.submitRatings(true)" class="bg-green-500 text-white px-6 py-2 rounded-md text-lg hover:bg-green-600 transition duration-300">Finish Survey</button>
                    `}
                </div>
            </div>

            <div class="text-center text-sm text-gray-600 mt-4">
                <p>Your progress: ${this.currentImageIndex + 1} / ${this.totalImages}</p>
            </div>
        `;
        document.getElementById('app').innerHTML = content;
    },

    submitRatings: function(isLast = false) {
        const ratings = {};
        this.currentModelOrder.forEach((model, index) => {
            const slider = document.getElementById(`slider-${index}`);
            ratings[model] = slider.value;
        });

        axios.post('save_ratings.php', {
            imageIndex: this.currentImageIndex,
            ratings: ratings
        }, {
            headers: {
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => {
            if (response.data.success) {
                console.log('Ratings saved successfully');
                if (isLast) {
                    this.showThankYouPage();
                } else {
                    this.loadImage(this.currentImageIndex + 1);
                }
            } else {
                throw new Error(response.data.error);
            }
        })
        .catch(error => {
            console.error('Error saving ratings:', error);
            alert('Error saving ratings: ' + error.message);
        });
    },

    showThankYouPage: function() {
        const content = `
            <h1 class="text-3xl font-bold text-center mb-8">Thank You for Your Participation!</h1>
            <div class="bg-white p-6 rounded-lg shadow-md">
                <p class="mb-4">Your evaluations have been successfully saved. Your input is invaluable for our research.</p>
                <p class="mb-4">Here's what happens next:</p>
                <ul class="list-disc list-inside space-y-2 mb-6">
                    <li>Your responses will be analyzed along with those of other participants.</li>
                    <li>The results will help improve image description technologies.</li>
                    <li>No personally identifiable information has been stored with your responses.</li>
                </ul>
                <p class="text-sm text-gray-600">If you have any questions about this study, please contact our research team at research@riespatrick.de.</p>
            </div>
            <div class="text-center mt-8">
                <button onclick="app.loadWelcomePage()" class="bg-blue-500 text-white px-6 py-3 rounded-lg hover:bg-blue-600 transition duration-300">
                    Back to Home
                </button>
            </div>
        `;
        document.getElementById('app').innerHTML = content;
    }
};

document.addEventListener('DOMContentLoaded', function() {
    app.init();
});