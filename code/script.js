// Set tanggal minimum ke hari ini
document.addEventListener("DOMContentLoaded", function() {
    const today = new Date();
    document.getElementById('date').setAttribute('min', today.toISOString().split('T')[0]);
    
    // Cek jika tanggal yang dipilih adalah hari ini
    const dateInput = document.getElementById('date');
    const timeInput = document.getElementById('time');

    dateInput.addEventListener('change', function() {
        const selectedDate = new Date(dateInput.value);
        if (selectedDate.toDateString() === today.toDateString()) {
            const currentTime = today.getHours() * 60 + today.getMinutes();
            const timeOptions = timeInput.querySelectorAll('option');

            timeOptions.forEach(option => {
                const [hours, minutes] = option.value.split(':').map(Number);
                const selectedTime = hours * 60 + minutes;

                // Nonaktifkan opsi waktu yang sudah terlewati
                option.disabled = selectedTime < currentTime;
            });
        }
    });
});

// Fungsi untuk menghitung waktu akhir berdasarkan durasi
function calculateEndTime() {
    const startTime = document.getElementById('time').value;
    const duration = document.getElementById('duration').value;
    const [hours, minutes] = startTime.split(':').map(Number);
    const endTime = new Date();
    endTime.setHours(hours + parseInt(duration), minutes);

    const endTimeString = endTime.toTimeString().substring(0, 5);
    document.getElementById('endTime').value = endTimeString;
}

// Fungsi untuk memperbarui harga berdasarkan durasi
function updatePrice() {
    const duration = parseInt(document.getElementById('duration').value, 10);
    const pricePerHour = 50000; // Harga per jam
    const totalPrice = duration * pricePerHour;
    document.getElementById('price').innerText = totalPrice.toLocaleString('id-ID');

    // Hitung waktu selesai setiap kali durasi diubah
    calculateEndTime();
}

// Slider
let currentIndex = 0; // Indeks slide saat ini

// Menampilkan slide berdasarkan indeks
function showSlide(index) {
    const slides = document.querySelectorAll('.slide');
    const dots = document.querySelectorAll('.dot');

    // Update current index berdasarkan indeks yang diberikan
    if (index >= slides.length) {
        currentIndex = 0;
    } else if (index < 0) {
        currentIndex = slides.length - 1;
    } else {
        currentIndex = index;
    }

    // Tampilkan slide saat ini dan sembunyikan yang lain
    slides.forEach((slide, i) => {
        slide.classList.toggle('active', i === currentIndex);
    });

    // Perbarui status aktif pada dots
    dots.forEach(dot => dot.classList.remove('active'));
    dots[currentIndex].classList.add('active');
}

// Mengubah slide berdasarkan arah (prev/next)
function changeSlide(direction) {
    showSlide(currentIndex + direction);
}

// Membuat dots untuk setiap slide
function createDots() {
    const dotContainer = document.getElementById('dotContainer');
    const slides = document.querySelectorAll('.slide');

    // Buat dots untuk setiap slide
    slides.forEach((_, index) => {
        const dot = document.createElement('span');
        dot.classList.add('dot');
        dot.onclick = () => showSlide(index); // Event listener untuk navigasi ke slide
        dotContainer.appendChild(dot);
    });

    // Set dot pertama sebagai aktif
    dotContainer.children[0].classList.add('active');
}

// Fungsi untuk menggulir ke bagian yang ditentukan
function scrollToSection(sectionId) {
    const section = document.getElementById(sectionId);
    section.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// Inisialisasi slider dan buat dots
createDots();
showSlide(currentIndex);

// Menambahkan event listener untuk item menu
document.querySelectorAll('nav a').forEach(link => {
    link.addEventListener('click', function(event) {
        event.preventDefault(); // Mencegah perilaku default anchor
        const targetId = this.getAttribute('href').substring(1); // Mendapatkan ID bagian target
        scrollToSection(targetId); // Gulir ke bagian target
    });
});

// Menambahkan event listener untuk input waktu dan durasi
document.getElementById('time').addEventListener('change', calculateEndTime);
document.getElementById('duration').addEventListener('change', updatePrice);