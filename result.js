/**
 * EduPredict - Prediction Result Screen Logic
 * Capstone Prototype Interactive Logic
 */

document.addEventListener('DOMContentLoaded', () => {
  // Animate horizontal bars on load
  const hBars = document.querySelectorAll('.h-bar-fill');
  hBars.forEach(bar => {
    const targetWidth = bar.style.getPropertyValue('--bar-width') || '20%';
    bar.style.width = '0%';
    setTimeout(() => {
      bar.style.width = targetWidth;
    }, 150);
  });

  // Load dynamic student input summary if stored from previous screen
  try {
    const storedData = sessionStorage.getItem('edupredict_current_student');
    if (storedData) {
      const payload = JSON.parse(storedData);
      const rec = payload.record || {};

      if (rec.studentId) document.getElementById('val-student-id').textContent = rec.studentId;
      if (rec.hoursStudied) document.getElementById('val-hours').textContent = `${rec.hoursStudied} hrs/wk`;
      if (rec.attendance) document.getElementById('val-attendance').textContent = `${rec.attendance}%`;
      if (rec.previousScores) document.getElementById('val-previous-scores').textContent = rec.previousScores;
      if (rec.tutoringSessions) document.getElementById('val-tutoring').textContent = rec.tutoringSessions;
      if (rec.sleepHours) document.getElementById('val-sleep').textContent = `${rec.sleepHours} hrs/day`;
      if (rec.physicalActivity) document.getElementById('val-activity').textContent = `${rec.physicalActivity} hrs/wk`;
      if (rec.parentalInvolvement) document.getElementById('val-parental').textContent = rec.parentalInvolvement;
      if (rec.accessResources) document.getElementById('val-resources').textContent = rec.accessResources;
      if (rec.motivationLevel) document.getElementById('val-motivation').textContent = rec.motivationLevel;
      if (rec.internetAccess) document.getElementById('val-internet').textContent = rec.internetAccess;
      if (rec.teacherQuality) document.getElementById('val-teacher').textContent = rec.teacherQuality;
      if (rec.familyIncome) document.getElementById('val-income').textContent = rec.familyIncome;
      if (rec.schoolType || rec.schoolCategory) document.getElementById('val-school-type').textContent = rec.schoolType || rec.schoolCategory;
      if (rec.peerInfluence) document.getElementById('val-peer').textContent = rec.peerInfluence;
      if (rec.learningDisabilities) document.getElementById('val-disabilities').textContent = rec.learningDisabilities;
      if (rec.parentEducation) document.getElementById('val-education').textContent = rec.parentEducation;
      if (rec.distanceHome) document.getElementById('val-distance').textContent = rec.distanceHome;
      if (rec.gender) document.getElementById('val-gender').textContent = rec.gender;
      if (rec.extracurricular) document.getElementById('val-extra').textContent = rec.extracurricular;
    }
  } catch (e) {
    console.warn('SessionStorage load error:', e);
  }

  // Handle "Save Result" Primary Button
  const saveBtn = document.getElementById('save-result-btn');
  const saveToast = document.getElementById('save-notification');

  saveBtn.addEventListener('click', () => {
    saveBtn.disabled = true;
    saveBtn.style.opacity = '0.7';
    saveToast.classList.remove('hidden');

    setTimeout(() => {
      saveBtn.disabled = false;
      saveBtn.style.opacity = '1';
    }, 1500);

    setTimeout(() => {
      saveToast.classList.add('hidden');
    }, 4500);
  });

  // Sidebar Prototype Links
  const prototypeLinks = document.querySelectorAll('.prototype-link');
  prototypeLinks.forEach(link => {
    link.addEventListener('click', (e) => {
      e.preventDefault();
      const moduleName = link.getAttribute('data-module') || 'Feature';
      alert(`[Capstone Prototype Notice]\n\nThe "${moduleName}" module is part of the ML training pipeline. You are currently viewing the Prediction Result page.`);
    });
  });

  // User Profile Dropdown
  const userProfile = document.querySelector('.user-profile-widget');
  if (userProfile) {
    userProfile.addEventListener('click', () => {
      alert('[User Profile: Admin / Academic Evaluator]\nLogged in with full inference and prediction privileges.');
    });
  }
});
