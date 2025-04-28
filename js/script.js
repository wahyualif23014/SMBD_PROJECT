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

// Typing Text Animation
const typingText = document.getElementById('typing-text');
const textToType = "Hand Picked Book to your door.";
let index = 0;

function typeWriter() {
   if (index < textToType.length) {
      typingText.innerHTML += textToType.charAt(index);
      index++;
      setTimeout(typeWriter, 10000); // speed typing
   }
}

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


const track = document.querySelector('.carousel-track');
const items = document.querySelectorAll('.carousel-item');
const modal = document.getElementById('modal');
const modalTitle = document.getElementById('modal-title');
const modalClose = document.getElementById('modal-close');

// Handle click carousel item
items.forEach(item => {
  item.addEventListener('click', () => {
    // Pause animasi
    track.style.animationPlayState = 'paused';
    const title = item.getAttribute('data-title');
    modalTitle.textContent = title;
    modal.style.display = 'flex';
  });
});

// Handle close modal
modalClose.addEventListener('click', closeModal);
modal.addEventListener('click', (e) => {
  if (e.target === modal) {
    closeModal();
  }
});

function closeModal() {
  modal.style.display = 'none';
  // Resume animasi
  track.style.animationPlayState = 'running';
}

