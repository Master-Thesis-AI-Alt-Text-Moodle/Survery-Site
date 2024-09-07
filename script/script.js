document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM fully loaded and parsed');
    fetchImages();
});

function fetchImages() {
    console.log('Fetching images...');
    fetch('get_images.php')
        .then(response => {
            console.log('Response received', response);
            return response.json();
        })
        .then(data => {
            console.log('Data parsed', data);
            const imageContainer = document.getElementById('image-container');
            if (data.images && data.images.length > 0) {
                data.images.forEach((image, index) => {
                    console.log('Creating element for image', image);
                    const imageElement = createImageElement(image, index);
                    imageContainer.appendChild(imageElement);
                });
            } else {
                console.log('No images found in the response');
                imageContainer.innerHTML = '<p>Keine Bilder gefunden.</p>';
            }
        })
        .catch(error => console.error('Error:', error));
}

function createImageElement(image, index) {
    console.log('Creating image element', image, index);
    const div = document.createElement('div');
    div.className = 'bg-white rounded-lg shadow-md p-6 image-card';
    div.innerHTML = `
        <img src="images/${image.filename}" alt="Bild ${index + 1}" class="w-full h-64 object-cover rounded-lg mb-4">
        <div class="space-y-4">
            ${image.texts.map((text, textIndex) => `
                <div>
                    <p class="font-semibold mb-2">${text}</p>
                    <input type="range" min="1" max="5" value="3" class="slider" id="slider-${index}-${textIndex}">
                    <div class="flex justify-between text-sm text-gray-600">
                        <span>Schlecht</span>
                        <span>Mittel</span>
                        <span>Gut</span>
                    </div>
                </div>
            `).join('')}
        </div>
        <button onclick="submitRatings(${index})" class="mt-4 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition duration-300">Bewertungen abschicken</button>
    `;
    return div;
}

function submitRatings(imageIndex) {
    const ratings = [];
    for (let i = 0; i < 5; i++) {
        const slider = document.getElementById(`slider-${imageIndex}-${i}`);
        ratings.push(slider.value);
    }

    fetch('save_ratings.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            imageIndex: imageIndex,
            ratings: ratings
        }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                title: 'Erfolg!',
                text: 'Bewertungen erfolgreich gespeichert!',
                icon: 'success',
                confirmButtonText: 'OK'
            });
        } else {
            throw new Error('Server responded with an error');
        }
    })
    .catch((error) => {
        console.error('Error:', error);
        Swal.fire({
            title: 'Fehler!',
            text: 'Fehler beim Speichern der Bewertungen.',
            icon: 'error',
            confirmButtonText: 'OK'
        });
    });
}