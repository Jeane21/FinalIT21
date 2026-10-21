/**
 * EduPredict - Predict Student Performance Screen Logic
 * Capstone Prototype Interactive Logic
 */

document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('prediction-form');
  const predictBtn = document.getElementById('predict-button');
  const btnLabel = predictBtn.querySelector('.btn-label');
  const btnSpinner = predictBtn.querySelector('.btn-spinner');
  const arrowIcon = predictBtn.querySelector('.arrow-right-icon');
  const clearBtn = document.getElementById('clear-form-btn');
  const loadSampleBtn = document.getElementById('load-sample-btn');
  const statusBar = document.getElementById('predict-status');
  const statusText = document.getElementById('predict-status-text');

  // Sample Data Values from the Specification
  const sampleData = {
    studentId: '2024-006',
    gender: 'Female',
    schoolType: 'Public',
    hoursStudied: '5',
    attendance: '90',
    previousScores: '78',
    tutoringSessions: '2',
    sleepHours: '7',
    physicalActivity: '4',
    parentalInvolvement: 'High',
    accessResources: 'High',
    motivationLevel: 'Medium',
    internetAccess: 'Yes',
    teacherQuality: 'High',
    extracurricular: 'Yes',
    familyIncome: 'Medium',
    peerInfluence: 'Positive',
    learningDisabilities: 'No',
    parentEducation: 'College',
    distanceHome: 'Near'
  };

  // 1. Load Sample Data handler
  loadSampleBtn.addEventListener('click', () => {
    Object.keys(sampleData).forEach(key => {
      const field = form.elements[key];
      if (field) {
        field.value = sampleData[key];
        // Flash animation for visual feedback
        field.style.backgroundColor = '#EBF2FE';
        setTimeout(() => {
          field.style.backgroundColor = '';
        }, 300);
      }
    });

    showStatusFeedback('Loaded benchmark student profile into all 18 factor fields.', 2500);
  });

  // 2. Clear Form handler
  clearBtn.addEventListener('click', () => {
    form.reset();
    showStatusFeedback('Form cleared. Please enter new student information.', 2000);
  });

  // 3. Form Submission & ML Inference Pipeline Execution
  form.addEventListener('submit', (e) => {
    e.preventDefault();

    // Collect all field values
    const formData = new FormData(form);
    const studentRecord = {};
    for (let [key, val] of formData.entries()) {
      studentRecord[key] = val;
    }

    // Validation
    if (!studentRecord.studentId) {
      alert('Please enter a valid Student ID.');
      document.getElementById('student-id').focus();
      return;
    }

    // Compute ML Model Classification (Decision Tree & Random Forest Simulation)
    // Based on academic factors: previousScores (0-100), attendance (0-100), hoursStudied, etc.
    const prevScore = parseFloat(studentRecord.previousScores) || 75;
    const attendance = parseFloat(studentRecord.attendance) || 85;
    const hours = parseFloat(studentRecord.hoursStudied) || 5;

    // Weighted academic index (Composite Score Calculation)
    const compositeScore = (prevScore * 0.5) + (attendance * 0.3) + (hours * 2.5);

    let predictionResult = 'Medium';
    let probabilityHigh = 25;
    let probabilityMedium = 60;
    let probabilityLow = 15;

    if (compositeScore >= 75) {
      predictionResult = 'High';
      probabilityHigh = Math.min(94, Math.round(55 + (compositeScore - 75) * 1.5));
      probabilityMedium = Math.round((100 - probabilityHigh) * 0.7);
      probabilityLow = 100 - probabilityHigh - probabilityMedium;
    } else if (compositeScore < 55) {
      predictionResult = 'Low';
      probabilityLow = Math.min(92, Math.round(50 + (55 - compositeScore) * 1.8));
      probabilityMedium = Math.round((100 - probabilityLow) * 0.75);
      probabilityHigh = 100 - probabilityLow - probabilityMedium;
    } else {
      predictionResult = 'Medium';
      probabilityMedium = 68;
      probabilityHigh = 20;
      probabilityLow = 12;
    }

    // Package the student record & prediction to be rendered on the NEXT screen
    const predictionPayload = {
      record: studentRecord,
      prediction: predictionResult,
      confidence: {
        high: probabilityHigh,
        medium: probabilityMedium,
        low: probabilityLow
      },
      models: {
        randomForest: predictionResult,
        decisionTree: predictionResult
      },
      timestamp: new Date().toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
    };

    // Store in SessionStorage for the Result Screen
    try {
      sessionStorage.setItem('edupredict_current_student', JSON.stringify(predictionPayload));
      localStorage.setItem('edupredict_latest_prediction', JSON.stringify(predictionPayload));
    } catch (err) {
      console.warn('Storage unavailable:', err);
    }

    // Set Loading & UI Feedback State
    setLoading(true);
    statusBar.classList.remove('hidden');
    statusText.textContent = `Running classification inference for Student ${studentRecord.studentId}...`;

    // Simulate ML processing time then redirect to the NEXT screen
    setTimeout(() => {
      statusText.textContent = 'Classification complete! Redirecting to Prediction Results...';
      
      setTimeout(() => {
        // Redirect to the prediction result screen
        window.location.href = 'result.html';
      }, 700);

    }, 1200);
  });

  // Helper UI functions
  function setLoading(isLoading) {
    if (isLoading) {
      predictBtn.disabled = true;
      btnSpinner.classList.remove('hidden');
      arrowIcon.style.display = 'none';
      btnLabel.textContent = 'Analyzing Student Factors...';
    } else {
      predictBtn.disabled = false;
      btnSpinner.classList.add('hidden');
      arrowIcon.style.display = '';
      btnLabel.textContent = 'Predict Performance';
    }
  }

  function showStatusFeedback(msg, duration = 3000) {
    statusBar.classList.remove('hidden');
    statusText.textContent = msg;
    setTimeout(() => {
      statusBar.classList.add('hidden');
    }, duration);
  }

  // Sidebar Prototype Links
  const prototypeLinks = document.querySelectorAll('.prototype-link');
  prototypeLinks.forEach(link => {
    link.addEventListener('click', (e) => {
      e.preventDefault();
      const moduleName = link.getAttribute('data-module') || 'Feature';
      alert(`[Capstone Prototype Notice]\n\nThe "${moduleName}" module is part of the ML training pipeline. You are currently viewing the Predict Student Performance page.`);
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
