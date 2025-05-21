let userBox = document.querySelector('.header .header-2 .user-box');

document.querySelector('#user-btn').onclick = () =>{
   userBox.classList.toggle('active');
   navbar.classList.remove('active');
}

let navbar = document.querySelector('.header .header-2 .navbar');

document.querySelector('#menu-btn').onclick = () =>{
   navbar.classList.toggle('active');
   userBox.classList.remove('active');
}

window.onscroll = () =>{
   userBox.classList.remove('active');
   navbar.classList.remove('active');

   if(window.scrollY > 60){
      document.querySelector('.header .header-2').classList.add('active');
   }else{
      document.querySelector('.header .header-2').classList.remove('active');
   }
}

// 
// Add event listeners
userBtn.addEventListener('click', toggleUser);
menuBtn.addEventListener('click', toggleNavbar);
window.addEventListener('scroll', handleScroll);

// Smooth scrolling for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();

        document.querySelector(this.getAttribute('href')).scrollIntoView({
            behavior: 'smooth'
        });
    });
});

// Toggle user box visibility
const toggleUserBox = () => {
   userBox.classList.toggle('active');
   navbar.classList.remove('active');
};

// Toggle navbar visibility
const toggleNavbar = () => {
   navbar.classList.toggle('active');
   userBox.classList.remove('active');
};

// Handle scroll events
const handleScroll = () => {
   userBox.classList.remove('active');
   navbar.classList.remove('active');

   if (window.scrollY > 60) {
       header.classList.add('active');
   } else {
       header.classList.remove('active');
   }
};

// Add event listeners
userBtn.addEventListener('click', toggleUserBox);
menuBtn.addEventListener('click', toggleNavbar);
window.addEventListener('scroll', handleScroll);

// home page 
// Smooth parallax movement effect



// Mouse Parallax Effect
document.addEventListener('mousemove', (e) => {
   const homeContent = document.querySelector('.home .content');
   const moveX = (e.clientX - window.innerWidth / 2) * 0.02;
   const moveY = (e.clientY - window.innerHeight / 2) * 0.02;
   homeContent.style.transform = `translate(${moveX}px, ${moveY}px)`;
});

// Scroll Reveal Animation
const homeContent = document.querySelector('.home .content');
window.addEventListener('scroll', () => {
   const scrollPosition = window.scrollY;
   const homeHeight = document.querySelector('.home').offsetHeight;

   if (scrollPosition < homeHeight) {
      homeContent.style.opacity = 1 - scrollPosition / (homeHeight / 2);
      homeContent.style.transform = `translateY(${scrollPosition * 0.3}px)`;
   }
});

// On Load Animation + Start Typing
window.addEventListener('load', () => {
   const homeContent = document.querySelector('.home .content');
   homeContent.style.transition = 'all 1s ease-out';
   homeContent.style.opacity = 1;
   typeWriter(); // Start typing after load
});

// promosi

// Carousel Animation`
class Carousel {
  constructor(container) {
    this.container = container;
    this.track = container.querySelector('.carousel-track');
    this.items = Array.from(container.querySelectorAll('.carousel-item'));
    this.prevBtn = container.querySelector('.carousel-button.prev');
    this.nextBtn = container.querySelector('.carousel-button.next');
    this.pagination = container.querySelector('.carousel-pagination');
    
    this.currentIndex = 0;
    this.itemWidth = this.items[0].getBoundingClientRect().width;
    this.gap = parseInt(window.getComputedStyle(this.track).gap) || 0;
    this.visibleItems = this.calculateVisibleItems();
    this.maxIndex = this.items.length - this.visibleItems;
    
    this.init();
    this.setupEventListeners();
    this.updateCarousel();
  }
  
  init() {
    // Create pagination dots
    this.items.forEach((_, index) => {
      const dot = document.createElement('button');
      dot.addEventListener('click', () => this.goToIndex(index));
      this.pagination.appendChild(dot);
    });
    
    // Enable smooth scrolling
    this.track.style.scrollBehavior = 'smooth';
    
    // Handle window resize
    window.addEventListener('resize', () => {
      this.itemWidth = this.items[0].getBoundingClientRect().width;
      this.visibleItems = this.calculateVisibleItems();
      this.maxIndex = Math.max(0, this.items.length - this.visibleItems);
      this.updateCarousel();
    });
  }
  
  calculateVisibleItems() {
    const containerWidth = this.container.querySelector('.carousel-container').offsetWidth;
    return Math.min(Math.floor(containerWidth / (this.itemWidth + this.gap)), this.items.length);
  }
  
  setupEventListeners() {
    // Navigation buttons
    this.prevBtn.addEventListener('click', () => this.prev());
    this.nextBtn.addEventListener('click', () => this.next());
    
    // Touch and mouse events for dragging
    let isDragging = false;
    let startPos = 0;
    let currentTranslate = 0;
    let prevTranslate = 0;
    let animationID;
    
    // Touch events
    this.track.addEventListener('touchstart', (e) => {
      isDragging = true;
      startPos = e.touches[0].clientX;
      this.track.style.scrollBehavior = 'auto';
      cancelAnimationFrame(animationID);
    });
    
    this.track.addEventListener('touchmove', (e) => {
      if (!isDragging) return;
      const currentPos = e.touches[0].clientX;
      const diff = currentPos - startPos;
      this.track.scrollLeft = this.track.scrollLeft - diff;
      startPos = currentPos;
    });
    
    this.track.addEventListener('touchend', () => {
      isDragging = false;
      this.track.style.scrollBehavior = 'smooth';
      this.snapToItem();
    });
    
    // Mouse events
    this.track.addEventListener('mousedown', (e) => {
      isDragging = true;
      startPos = e.clientX;
      this.track.style.scrollBehavior = 'auto';
      cancelAnimationFrame(animationID);
      e.preventDefault(); // Prevent text selection
    });
    
    window.addEventListener('mousemove', (e) => {
      if (!isDragging) return;
      const currentPos = e.clientX;
      const diff = currentPos - startPos;
      this.track.scrollLeft = this.track.scrollLeft - diff;
      startPos = currentPos;
    });
    
    window.addEventListener('mouseup', () => {
      if (isDragging) {
        isDragging = false;
        this.track.style.scrollBehavior = 'smooth';
        this.snapToItem();
      }
    });
  }
  
  snapToItem() {
    const scrollPosition = this.track.scrollLeft;
    this.currentIndex = Math.round(scrollPosition / (this.itemWidth + this.gap));
    this.currentIndex = Math.max(0, Math.min(this.currentIndex, this.maxIndex));
    this.updateCarousel();
  }
  
  updateCarousel() {
    // Update navigation buttons
    this.prevBtn.disabled = this.currentIndex === 0;
    this.nextBtn.disabled = this.currentIndex >= this.maxIndex;
    
    // Update pagination
    const dots = this.pagination.querySelectorAll('button');
    dots.forEach((dot, index) => {
      dot.classList.toggle('active', index === this.currentIndex);
    });
    
    // Scroll to current item
    this.track.scrollTo({
      left: this.currentIndex * (this.itemWidth + this.gap),
      behavior: 'smooth'
    });
  }
  
  prev() {
    if (this.currentIndex > 0) {
      this.currentIndex--;
      this.updateCarousel();
    }
  }
  
  next() {
    if (this.currentIndex < this.maxIndex) {
      this.currentIndex++;
      this.updateCarousel();
    }
  }
  
  goToIndex(index) {
    this.currentIndex = Math.max(0, Math.min(index, this.maxIndex));
    this.updateCarousel();
  }
}

// Initialize all carousels on the page
document.querySelectorAll('.promo-carousel').forEach(carousel => {
  new Carousel(carousel);
});

// Handle close modal
modalClose.addEventListener('click', closeModal);
modal.addEventListener('click', (e) => {
  if (e.target === modal) {
    closeModal();
  }
});

const canvas = document.getElementById('background-canvas');
const ctx = canvas.getContext('2d');

let width, height;
function resizeCanvas() {
  width = canvas.width = window.innerWidth;
  height = canvas.height = window.innerHeight;
}
window.addEventListener('resize', resizeCanvas);
resizeCanvas();

// Tangkap posisi mouse
let mouseX = width / 2;
document.addEventListener('mousemove', e => {
  mouseX = e.clientX;
});

// Bintang jatuh
class Star {
  constructor() {
    this.reset();
  }

  reset() {
    this.x = Math.random() * width;
    this.y = Math.random() * -height;
    this.length = Math.random() * 80 + 50;
    this.speed = Math.random() * 6 + 4;
    this.opacity = Math.random() * 0.6 + 0.4;
    this.angle = Math.PI / 4;
  }

  update() {
    this.x += this.speed * Math.cos(this.angle);
    this.y += this.speed * Math.sin(this.angle);
    if (this.x > width || this.y > height) this.reset();
  }

  draw() {
    ctx.beginPath();
    ctx.moveTo(this.x, this.y);
    ctx.lineTo(this.x - this.length * Math.cos(this.angle), this.y - this.length * Math.sin(this.angle));
    ctx.strokeStyle = `rgba(255, 255, 255, ${this.opacity})`;
    ctx.lineWidth = 1.5;
    ctx.stroke();
  }
}

// Aurora dengan gelombang horizontal
function drawAurora(time) {
  const waveHeight = 50;
  const waveCount = 3;
  const offsetX = (mouseX / width - 0.5) * 100; // efek parallax mouse

  for (let i = 0; i < waveCount; i++) {
    ctx.beginPath();
    ctx.moveTo(0, height / 2);

    for (let x = 0; x <= width; x += 10) {
      let y = Math.sin((x + time * 0.1 + i * 100) * 0.01) * waveHeight + height / 2 + i * 20;
      ctx.lineTo(x, y);
    }

    let r = Math.sin(time * 0.001 + i) * 127 + 128;
    let g = Math.sin(time * 0.001 + 2 + i) * 127 + 128;
    let b = Math.sin(time * 0.001 + 4 + i) * 127 + 128;

    ctx.strokeStyle = `rgba(${r}, ${g}, ${b}, 0.05)`;
    ctx.lineWidth = 60;
    ctx.shadowColor = `rgba(${r}, ${g}, ${b}, 0.2)`;
    ctx.shadowBlur = 30;
    ctx.stroke();
  }

  // reset shadow
  ctx.shadowBlur = 0;
  ctx.shadowColor = "transparent";
}

let stars = [];
for (let i = 0; i < 50; i++) {
  stars.push(new Star());
}

function animate(time) {
  ctx.clearRect(0, 0, width, height);
  drawAurora(time);
  for (let star of stars) {
    star.update();
    star.draw();
  }
  requestAnimationFrame(animate);
}

animate();
