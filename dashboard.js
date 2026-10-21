/**
 * EduPredict - Student Performance Prediction Dashboard Logic
 * Capstone Prototype Interactive Logic
 */

document.addEventListener('DOMContentLoaded', () => {
  // Animate the bar chart on load
  const bars = document.querySelectorAll('.bar-fill');
  
  bars.forEach(bar => {
    const targetHeight = bar.style.getPropertyValue('--bar-height') || '50%';
    // Start at 0 height and smoothly grow
    bar.style.height = '0%';
    setTimeout(() => {
      bar.style.height = targetHeight;
    }, 150);
  });

  // Prototype links feedback (for realistic capstone walkthrough)
  const prototypeLinks = document.querySelectorAll('.prototype-link');
  prototypeLinks.forEach(link => {
    link.addEventListener('click', (e) => {
      e.preventDefault();
      const moduleName = link.getAttribute('data-module') || 'Feature';
      alert(`[Capstone Prototype Notice]\n\nThe "${moduleName}" module is part of the ML training pipeline. You are currently viewing the primary Academic Performance Prediction Dashboard.`);
    });
  });

  // User Profile Widget click
  const userProfile = document.querySelector('.user-profile-widget');
  if (userProfile) {
    userProfile.addEventListener('click', () => {
      alert('[User Profile: Admin / Academic Evaluator]\nLogged in with full classification model evaluation privileges.');
    });
  }
});
