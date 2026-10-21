const summaryConfig = {
  totalStudents: 6607,
  low: { value: 1452, percent: '21.98%' },
  medium: { value: 3530, percent: '53.43%' },
  high: { value: 1625, percent: '24.60%' }
};

const studentRows = [
  { studentId: 'STU-1001', gender: 'Female', schoolType: 'Public', hoursStudied: 4.8, attendance: 86, previousScores: 74, tutoringSessions: 2, sleepHours: 7.1, physicalActivity: 4, parentalInvolvement: 'High', accessToResources: 'Adequate', extracurricularActivities: 'Yes', motivationLevel: 'High', internetAccess: 'Yes', familyIncome: 'Middle', teacherQuality: 'Good', peerInfluence: 'Positive', learningDisabilities: 'No', parentalEducationLevel: 'College', distanceFromHome: 14, performanceLevel: 'Medium', predictionModel: 'Random Forest', predictionDateTime: '2026-10-04 09:18' },
  { studentId: 'STU-1002', gender: 'Male', schoolType: 'Public', hoursStudied: 5.2, attendance: 91, previousScores: 81, tutoringSessions: 3, sleepHours: 7.5, physicalActivity: 5, parentalInvolvement: 'High', accessToResources: 'Adequate', extracurricularActivities: 'Yes', motivationLevel: 'High', internetAccess: 'Yes', familyIncome: 'Middle', teacherQuality: 'Very Good', peerInfluence: 'Positive', learningDisabilities: 'No', parentalEducationLevel: 'University', distanceFromHome: 11, performanceLevel: 'High', predictionModel: 'Random Forest', predictionDateTime: '2026-10-04 09:22' },
  { studentId: 'STU-1003', gender: 'Female', schoolType: 'Private', hoursStudied: 4.5, attendance: 88, previousScores: 72, tutoringSessions: 2, sleepHours: 7.0, physicalActivity: 4, parentalInvolvement: 'Moderate', accessToResources: 'High', extracurricularActivities: 'Yes', motivationLevel: 'Moderate', internetAccess: 'Yes', familyIncome: 'High', teacherQuality: 'Very Good', peerInfluence: 'Neutral', learningDisabilities: 'No', parentalEducationLevel: 'College', distanceFromHome: 9, performanceLevel: 'Medium', predictionModel: 'Random Forest', predictionDateTime: '2026-10-04 09:24' },
  { studentId: 'STU-1004', gender: 'Male', schoolType: 'Public', hoursStudied: 3.4, attendance: 74, previousScores: 63, tutoringSessions: 1, sleepHours: 6.3, physicalActivity: 3, parentalInvolvement: 'Low', accessToResources: 'Limited', extracurricularActivities: 'No', motivationLevel: 'Low', internetAccess: 'Partial', familyIncome: 'Low', teacherQuality: 'Fair', peerInfluence: 'Negative', learningDisabilities: 'No', parentalEducationLevel: 'Secondary', distanceFromHome: 22, performanceLevel: 'Low', predictionModel: 'Decision Tree', predictionDateTime: '2026-10-04 09:30' },
  { studentId: 'STU-1005', gender: 'Female', schoolType: 'Private', hoursStudied: 5.5, attendance: 94, previousScores: 83, tutoringSessions: 3, sleepHours: 7.8, physicalActivity: 5, parentalInvolvement: 'High', accessToResources: 'High', extracurricularActivities: 'Yes', motivationLevel: 'High', internetAccess: 'Yes', familyIncome: 'High', teacherQuality: 'Excellent', peerInfluence: 'Positive', learningDisabilities: 'No', parentalEducationLevel: 'University', distanceFromHome: 8, performanceLevel: 'High', predictionModel: 'Random Forest', predictionDateTime: '2026-10-04 09:31' },
  { studentId: 'STU-1006', gender: 'Male', schoolType: 'Public', hoursStudied: 3.8, attendance: 81, previousScores: 67, tutoringSessions: 2, sleepHours: 6.5, physicalActivity: 3, parentalInvolvement: 'Moderate', accessToResources: 'Adequate', extracurricularActivities: 'No', motivationLevel: 'Moderate', internetAccess: 'Yes', familyIncome: 'Middle', teacherQuality: 'Good', peerInfluence: 'Neutral', learningDisabilities: 'No', parentalEducationLevel: 'High School', distanceFromHome: 17, performanceLevel: 'Medium', predictionModel: 'Random Forest', predictionDateTime: '2026-10-04 09:34' },
  { studentId: 'STU-1007', gender: 'Female', schoolType: 'Public', hoursStudied: 2.9, attendance: 68, previousScores: 58, tutoringSessions: 1, sleepHours: 6.1, physicalActivity: 2, parentalInvolvement: 'Low', accessToResources: 'Limited', extracurricularActivities: 'No', motivationLevel: 'Low', internetAccess: 'Partial', familyIncome: 'Low', teacherQuality: 'Fair', peerInfluence: 'Negative', learningDisabilities: 'Yes', parentalEducationLevel: 'Secondary', distanceFromHome: 27, performanceLevel: 'Low', predictionModel: 'Decision Tree', predictionDateTime: '2026-10-04 09:38' },
  { studentId: 'STU-1008', gender: 'Male', schoolType: 'Private', hoursStudied: 5.1, attendance: 92, previousScores: 78, tutoringSessions: 3, sleepHours: 7.7, physicalActivity: 5, parentalInvolvement: 'High', accessToResources: 'High', extracurricularActivities: 'Yes', motivationLevel: 'High', internetAccess: 'Yes', familyIncome: 'Middle', teacherQuality: 'Excellent', peerInfluence: 'Positive', learningDisabilities: 'No', parentalEducationLevel: 'University', distanceFromHome: 7, performanceLevel: 'High', predictionModel: 'Random Forest', predictionDateTime: '2026-10-04 09:43' },
  { studentId: 'STU-1009', gender: 'Female', schoolType: 'Public', hoursStudied: 4.0, attendance: 80, previousScores: 70, tutoringSessions: 2, sleepHours: 7.2, physicalActivity: 3, parentalInvolvement: 'Moderate', accessToResources: 'Adequate', extracurricularActivities: 'Yes', motivationLevel: 'Moderate', internetAccess: 'Yes', familyIncome: 'Middle', teacherQuality: 'Good', peerInfluence: 'Positive', learningDisabilities: 'No', parentalEducationLevel: 'College', distanceFromHome: 13, performanceLevel: 'Medium', predictionModel: 'Random Forest', predictionDateTime: '2026-10-04 09:45' },
  { studentId: 'STU-1010', gender: 'Other', schoolType: 'Private', hoursStudied: 3.6, attendance: 79, previousScores: 66, tutoringSessions: 2, sleepHours: 6.9, physicalActivity: 3, parentalInvolvement: 'Moderate', accessToResources: 'Adequate', extracurricularActivities: 'Yes', motivationLevel: 'Moderate', internetAccess: 'Yes', familyIncome: 'Middle', teacherQuality: 'Good', peerInfluence: 'Neutral', learningDisabilities: 'No', parentalEducationLevel: 'College', distanceFromHome: 12, performanceLevel: 'Medium', predictionModel: 'Decision Tree', predictionDateTime: '2026-10-04 09:50' },
  { studentId: 'STU-1011', gender: 'Male', schoolType: 'Public', hoursStudied: 5.8, attendance: 96, previousScores: 85, tutoringSessions: 4, sleepHours: 8.1, physicalActivity: 5, parentalInvolvement: 'High', accessToResources: 'High', extracurricularActivities: 'Yes', motivationLevel: 'High', internetAccess: 'Yes', familyIncome: 'High', teacherQuality: 'Excellent', peerInfluence: 'Positive', learningDisabilities: 'No', parentalEducationLevel: 'University', distanceFromHome: 5, performanceLevel: 'High', predictionModel: 'Random Forest', predictionDateTime: '2026-10-04 09:52' },
  { studentId: 'STU-1012', gender: 'Female', schoolType: 'Public', hoursStudied: 3.2, attendance: 73, previousScores: 60, tutoringSessions: 1, sleepHours: 6.4, physicalActivity: 2, parentalInvolvement: 'Low', accessToResources: 'Limited', extracurricularActivities: 'No', motivationLevel: 'Low', internetAccess: 'Partial', familyIncome: 'Low', teacherQuality: 'Fair', peerInfluence: 'Negative', learningDisabilities: 'Yes', parentalEducationLevel: 'Secondary', distanceFromHome: 30, performanceLevel: 'Low', predictionModel: 'Decision Tree', predictionDateTime: '2026-10-04 09:54' },
  { studentId: 'STU-1013', gender: 'Female', schoolType: 'Private', hoursStudied: 4.7, attendance: 89, previousScores: 73, tutoringSessions: 2, sleepHours: 7.3, physicalActivity: 4, parentalInvolvement: 'High', accessToResources: 'Adequate', extracurricularActivities: 'Yes', motivationLevel: 'Moderate', internetAccess: 'Yes', familyIncome: 'Middle', teacherQuality: 'Good', peerInfluence: 'Positive', learningDisabilities: 'No', parentalEducationLevel: 'College', distanceFromHome: 10, performanceLevel: 'Medium', predictionModel: 'Random Forest', predictionDateTime: '2026-10-04 09:57' },
  { studentId: 'STU-1014', gender: 'Male', schoolType: 'Public', hoursStudied: 6.1, attendance: 97, previousScores: 88, tutoringSessions: 4, sleepHours: 8.0, physicalActivity: 6, parentalInvolvement: 'High', accessToResources: 'High', extracurricularActivities: 'Yes', motivationLevel: 'High', internetAccess: 'Yes', familyIncome: 'High', teacherQuality: 'Excellent', peerInfluence: 'Positive', learningDisabilities: 'No', parentalEducationLevel: 'University', distanceFromHome: 4, performanceLevel: 'High', predictionModel: 'Random Forest', predictionDateTime: '2026-10-04 10:00' },
  { studentId: 'STU-1015', gender: 'Male', schoolType: 'Private', hoursStudied: 3.9, attendance: 77, previousScores: 68, tutoringSessions: 2, sleepHours: 6.8, physicalActivity: 4, parentalInvolvement: 'Moderate', accessToResources: 'Adequate', extracurricularActivities: 'Yes', motivationLevel: 'Moderate', internetAccess: 'Yes', familyIncome: 'Middle', teacherQuality: 'Good', peerInfluence: 'Neutral', learningDisabilities: 'No', parentalEducationLevel: 'College', distanceFromHome: 15, performanceLevel: 'Medium', predictionModel: 'Decision Tree', predictionDateTime: '2026-10-04 10:03' },
  { studentId: 'STU-1016', gender: 'Female', schoolType: 'Private', hoursStudied: 4.9, attendance: 90, previousScores: 76, tutoringSessions: 3, sleepHours: 7.4, physicalActivity: 4, parentalInvolvement: 'High', accessToResources: 'Adequate', extracurricularActivities: 'Yes', motivationLevel: 'High', internetAccess: 'Yes', familyIncome: 'Middle', teacherQuality: 'Very Good', peerInfluence: 'Positive', learningDisabilities: 'No', parentalEducationLevel: 'College', distanceFromHome: 9, performanceLevel: 'Medium', predictionModel: 'Random Forest', predictionDateTime: '2026-10-04 10:08' },
  { studentId: 'STU-1017', gender: 'Female', schoolType: 'Public', hoursStudied: 2.7, attendance: 69, previousScores: 57, tutoringSessions: 1, sleepHours: 6.0, physicalActivity: 2, parentalInvolvement: 'Low', accessToResources: 'Limited', extracurricularActivities: 'No', motivationLevel: 'Low', internetAccess: 'Partial', familyIncome: 'Low', teacherQuality: 'Fair', peerInfluence: 'Negative', learningDisabilities: 'Yes', parentalEducationLevel: 'Secondary', distanceFromHome: 29, performanceLevel: 'Low', predictionModel: 'Decision Tree', predictionDateTime: '2026-10-04 10:15' },
  { studentId: 'STU-1018', gender: 'Male', schoolType: 'Public', hoursStudied: 5.0, attendance: 87, previousScores: 71, tutoringSessions: 2, sleepHours: 7.2, physicalActivity: 4, parentalInvolvement: 'Moderate', accessToResources: 'Adequate', extracurricularActivities: 'Yes', motivationLevel: 'Moderate', internetAccess: 'Yes', familyIncome: 'Middle', teacherQuality: 'Good', peerInfluence: 'Positive', learningDisabilities: 'No', parentalEducationLevel: 'College', distanceFromHome: 12, performanceLevel: 'Medium', predictionModel: 'Random Forest', predictionDateTime: '2026-10-04 10:18' },
  { studentId: 'STU-1019', gender: 'Female', schoolType: 'Private', hoursStudied: 5.6, attendance: 95, previousScores: 86, tutoringSessions: 3, sleepHours: 8.0, physicalActivity: 5, parentalInvolvement: 'High', accessToResources: 'High', extracurricularActivities: 'Yes', motivationLevel: 'High', internetAccess: 'Yes', familyIncome: 'High', teacherQuality: 'Excellent', peerInfluence: 'Positive', learningDisabilities: 'No', parentalEducationLevel: 'University', distanceFromHome: 6, performanceLevel: 'High', predictionModel: 'Random Forest', predictionDateTime: '2026-10-04 10:20' },
  { studentId: 'STU-1020', gender: 'Male', schoolType: 'Public', hoursStudied: 3.7, attendance: 78, previousScores: 65, tutoringSessions: 2, sleepHours: 6.6, physicalActivity: 3, parentalInvolvement: 'Moderate', accessToResources: 'Adequate', extracurricularActivities: 'No', motivationLevel: 'Low', internetAccess: 'Yes', familyIncome: 'Middle', teacherQuality: 'Fair', peerInfluence: 'Neutral', learningDisabilities: 'No', parentalEducationLevel: 'High School', distanceFromHome: 18, performanceLevel: 'Medium', predictionModel: 'Decision Tree', predictionDateTime: '2026-10-04 10:24' },
  { studentId: 'STU-1021', gender: 'Female', schoolType: 'Public', hoursStudied: 4.3, attendance: 84, previousScores: 75, tutoringSessions: 2, sleepHours: 7.1, physicalActivity: 4, parentalInvolvement: 'Moderate', accessToResources: 'Adequate', extracurricularActivities: 'Yes', motivationLevel: 'Moderate', internetAccess: 'Yes', familyIncome: 'Middle', teacherQuality: 'Good', peerInfluence: 'Positive', learningDisabilities: 'No', parentalEducationLevel: 'College', distanceFromHome: 10, performanceLevel: 'Medium', predictionModel: 'Random Forest', predictionDateTime: '2026-10-04 10:27' },
  { studentId: 'STU-1022', gender: 'Male', schoolType: 'Private', hoursStudied: 5.3, attendance: 94, previousScores: 80, tutoringSessions: 3, sleepHours: 7.6, physicalActivity: 5, parentalInvolvement: 'High', accessToResources: 'High', extracurricularActivities: 'Yes', motivationLevel: 'High', internetAccess: 'Yes', familyIncome: 'High', teacherQuality: 'Very Good', peerInfluence: 'Positive', learningDisabilities: 'No', parentalEducationLevel: 'University', distanceFromHome: 7, performanceLevel: 'High', predictionModel: 'Random Forest', predictionDateTime: '2026-10-04 10:29' },
  { studentId: 'STU-1023', gender: 'Female', schoolType: 'Public', hoursStudied: 3.1, attendance: 72, previousScores: 62, tutoringSessions: 1, sleepHours: 6.2, physicalActivity: 2, parentalInvolvement: 'Low', accessToResources: 'Limited', extracurricularActivities: 'No', motivationLevel: 'Low', internetAccess: 'Partial', familyIncome: 'Low', teacherQuality: 'Fair', peerInfluence: 'Negative', learningDisabilities: 'Yes', parentalEducationLevel: 'Secondary', distanceFromHome: 31, performanceLevel: 'Low', predictionModel: 'Decision Tree', predictionDateTime: '2026-10-04 10:33' },
  { studentId: 'STU-1024', gender: 'Female', schoolType: 'Private', hoursStudied: 4.6, attendance: 90, previousScores: 74, tutoringSessions: 2, sleepHours: 7.3, physicalActivity: 4, parentalInvolvement: 'Moderate', accessToResources: 'High', extracurricularActivities: 'Yes', motivationLevel: 'Moderate', internetAccess: 'Yes', familyIncome: 'Middle', teacherQuality: 'Very Good', peerInfluence: 'Positive', learningDisabilities: 'No', parentalEducationLevel: 'College', distanceFromHome: 11, performanceLevel: 'Medium', predictionModel: 'Random Forest', predictionDateTime: '2026-10-04 10:36' },
  { studentId: 'STU-1025', gender: 'Male', schoolType: 'Public', hoursStudied: 3.5, attendance: 87, previousScores: 70, tutoringSessions: 2, sleepHours: 6.7, physicalActivity: 3, parentalInvolvement: 'Moderate', accessToResources: 'Adequate', extracurricularActivities: 'Yes', motivationLevel: 'Moderate', internetAccess: 'Yes', familyIncome: 'Middle', teacherQuality: 'Good', peerInfluence: 'Neutral', learningDisabilities: 'No', parentalEducationLevel: 'High School', distanceFromHome: 14, performanceLevel: 'Medium', predictionModel: 'Random Forest', predictionDateTime: '2026-10-04 10:38' },
  { studentId: 'STU-1026', gender: 'Female', schoolType: 'Private', hoursStudied: 5.4, attendance: 96, previousScores: 87, tutoringSessions: 3, sleepHours: 8.1, physicalActivity: 5, parentalInvolvement: 'High', accessToResources: 'High', extracurricularActivities: 'Yes', motivationLevel: 'High', internetAccess: 'Yes', familyIncome: 'High', teacherQuality: 'Excellent', peerInfluence: 'Positive', learningDisabilities: 'No', parentalEducationLevel: 'University', distanceFromHome: 4, performanceLevel: 'High', predictionModel: 'Random Forest', predictionDateTime: '2026-10-04 10:41' },
  { studentId: 'STU-1027', gender: 'Male', schoolType: 'Public', hoursStudied: 3.0, attendance: 67, previousScores: 59, tutoringSessions: 1, sleepHours: 6.1, physicalActivity: 2, parentalInvolvement: 'Low', accessToResources: 'Limited', extracurricularActivities: 'No', motivationLevel: 'Low', internetAccess: 'Partial', familyIncome: 'Low', teacherQuality: 'Fair', peerInfluence: 'Negative', learningDisabilities: 'Yes', parentalEducationLevel: 'Secondary', distanceFromHome: 26, performanceLevel: 'Low', predictionModel: 'Decision Tree', predictionDateTime: '2026-10-04 10:44' },
  { studentId: 'STU-1028', gender: 'Female', schoolType: 'Private', hoursStudied: 4.4, attendance: 82, previousScores: 69, tutoringSessions: 2, sleepHours: 7.0, physicalActivity: 4, parentalInvolvement: 'Moderate', accessToResources: 'Adequate', extracurricularActivities: 'Yes', motivationLevel: 'Moderate', internetAccess: 'Yes', familyIncome: 'Middle', teacherQuality: 'Good', peerInfluence: 'Neutral', learningDisabilities: 'No', parentalEducationLevel: 'College', distanceFromHome: 13, performanceLevel: 'Medium', predictionModel: 'Decision Tree', predictionDateTime: '2026-10-04 10:50' },
  { studentId: 'STU-1029', gender: 'Male', schoolType: 'Public', hoursStudied: 5.7, attendance: 93, previousScores: 82, tutoringSessions: 3, sleepHours: 7.9, physicalActivity: 4, parentalInvolvement: 'High', accessToResources: 'High', extracurricularActivities: 'Yes', motivationLevel: 'High', internetAccess: 'Yes', familyIncome: 'High', teacherQuality: 'Very Good', peerInfluence: 'Positive', learningDisabilities: 'No', parentalEducationLevel: 'College', distanceFromHome: 8, performanceLevel: 'High', predictionModel: 'Random Forest', predictionDateTime: '2026-10-04 10:55' },
  { studentId: 'STU-1030', gender: 'Other', schoolType: 'Public', hoursStudied: 3.6, attendance: 76, previousScores: 64, tutoringSessions: 1, sleepHours: 6.5, physicalActivity: 3, parentalInvolvement: 'Moderate', accessToResources: 'Adequate', extracurricularActivities: 'Yes', motivationLevel: 'Moderate', internetAccess: 'Partial', familyIncome: 'Middle', teacherQuality: 'Fair', peerInfluence: 'Neutral', learningDisabilities: 'No', parentalEducationLevel: 'High School', distanceFromHome: 20, performanceLevel: 'Low', predictionModel: 'Decision Tree', predictionDateTime: '2026-10-04 10:56' },
  { studentId: 'STU-1031', gender: 'Female', schoolType: 'Private', hoursStudied: 5.2, attendance: 92, previousScores: 79, tutoringSessions: 3, sleepHours: 7.4, physicalActivity: 5, parentalInvolvement: 'High', accessToResources: 'High', extracurricularActivities: 'Yes', motivationLevel: 'High', internetAccess: 'Yes', familyIncome: 'High', teacherQuality: 'Excellent', peerInfluence: 'Positive', learningDisabilities: 'No', parentalEducationLevel: 'University', distanceFromHome: 5, performanceLevel: 'High', predictionModel: 'Random Forest', predictionDateTime: '2026-10-04 11:00' },
  { studentId: 'STU-1032', gender: 'Male', schoolType: 'Public', hoursStudied: 4.0, attendance: 80, previousScores: 68, tutoringSessions: 2, sleepHours: 6.9, physicalActivity: 3, parentalInvolvement: 'Moderate', accessToResources: 'Adequate', extracurricularActivities: 'No', motivationLevel: 'Moderate', internetAccess: 'Yes', familyIncome: 'Middle', teacherQuality: 'Good', peerInfluence: 'Neutral', learningDisabilities: 'No', parentalEducationLevel: 'High School', distanceFromHome: 17, performanceLevel: 'Medium', predictionModel: 'Random Forest', predictionDateTime: '2026-10-04 11:04' },
  { studentId: 'STU-1033', gender: 'Female', schoolType: 'Public', hoursStudied: 2.8, attendance: 66, previousScores: 56, tutoringSessions: 1, sleepHours: 5.9, physicalActivity: 2, parentalInvolvement: 'Low', accessToResources: 'Limited', extracurricularActivities: 'No', motivationLevel: 'Low', internetAccess: 'Partial', familyIncome: 'Low', teacherQuality: 'Fair', peerInfluence: 'Negative', learningDisabilities: 'Yes', parentalEducationLevel: 'Secondary', distanceFromHome: 28, performanceLevel: 'Low', predictionModel: 'Decision Tree', predictionDateTime: '2026-10-04 11:10' },
  { studentId: 'STU-1034', gender: 'Male', schoolType: 'Private', hoursStudied: 5.1, attendance: 91, previousScores: 77, tutoringSessions: 3, sleepHours: 7.5, physicalActivity: 5, parentalInvolvement: 'High', accessToResources: 'High', extracurricularActivities: 'Yes', motivationLevel: 'High', internetAccess: 'Yes', familyIncome: 'High', teacherQuality: 'Very Good', peerInfluence: 'Positive', learningDisabilities: 'No', parentalEducationLevel: 'University', distanceFromHome: 8, performanceLevel: 'High', predictionModel: 'Random Forest', predictionDateTime: '2026-10-04 11:13' },
  { studentId: 'STU-1035', gender: 'Female', schoolType: 'Public', hoursStudied: 4.2, attendance: 83, previousScores: 71, tutoringSessions: 2, sleepHours: 7.0, physicalActivity: 4, parentalInvolvement: 'Moderate', accessToResources: 'Adequate', extracurricularActivities: 'Yes', motivationLevel: 'Moderate', internetAccess: 'Yes', familyIncome: 'Middle', teacherQuality: 'Good', peerInfluence: 'Positive', learningDisabilities: 'No', parentalEducationLevel: 'College', distanceFromHome: 12, performanceLevel: 'Medium', predictionModel: 'Random Forest', predictionDateTime: '2026-10-04 11:17' }
];

const state = {
  currentPage: 1,
  rowsPerPage: 10,
  sortBy: null,
  sortDirection: 'asc',
  search: '',
  filters: {
    performance: 'All',
    gender: 'All',
    schoolType: 'All',
    model: 'All'
  }
};

const elements = {
  search: document.getElementById('student-search'),
  performance: document.getElementById('filter-performance'),
  gender: document.getElementById('filter-gender'),
  schoolType: document.getElementById('filter-school-type'),
  model: document.getElementById('filter-model'),
  rowsPerPage: document.getElementById('rows-per-page'),
  tableBody: document.getElementById('students-table-body'),
  rowsCount: document.getElementById('rows-count'),
  totalCount: document.getElementById('total-count'),
  loading: document.getElementById('table-loading'),
  empty: document.getElementById('table-empty'),
  error: document.getElementById('table-error'),
  pagination: document.getElementById('pagination'),
  modal: document.getElementById('student-modal'),
  modalContent: document.getElementById('modal-content')
};

document.addEventListener('DOMContentLoaded', () => {
  bindEvents();
  populateSummaryCards();
  showLoadingState();
  setTimeout(() => {
    renderTable();
  }, 300);
});

function bindEvents() {
  elements.search.addEventListener('input', (event) => {
    state.search = event.target.value.trim();
    state.currentPage = 1;
    renderTable();
  });

  elements.performance.addEventListener('change', (event) => {
    state.filters.performance = event.target.value;
    state.currentPage = 1;
    renderTable();
  });

  elements.gender.addEventListener('change', (event) => {
    state.filters.gender = event.target.value;
    state.currentPage = 1;
    renderTable();
  });

  elements.schoolType.addEventListener('change', (event) => {
    state.filters.schoolType = event.target.value;
    state.currentPage = 1;
    renderTable();
  });

  elements.model.addEventListener('change', (event) => {
    state.filters.model = event.target.value;
    state.currentPage = 1;
    renderTable();
  });

  elements.rowsPerPage.addEventListener('change', (event) => {
    state.rowsPerPage = Number(event.target.value);
    state.currentPage = 1;
    renderTable();
  });

  document.querySelectorAll('.sort-button').forEach((button) => {
    button.addEventListener('click', () => {
      const nextSortKey = button.dataset.sort;
      if (state.sortBy === nextSortKey) {
        state.sortDirection = state.sortDirection === 'asc' ? 'desc' : 'asc';
      } else {
        state.sortBy = nextSortKey;
        state.sortDirection = 'asc';
      }
      renderTable();
    });
  });

  elements.modal.addEventListener('click', (event) => {
    if (event.target.dataset.close === 'true' || event.target.closest('.modal-close')) {
      closeModal();
    }
  });
}

function populateSummaryCards() {
  document.getElementById('total-students').textContent = summaryConfig.totalStudents.toLocaleString();
  document.getElementById('low-count').textContent = summaryConfig.low.value.toLocaleString();
  document.getElementById('medium-count').textContent = summaryConfig.medium.value.toLocaleString();
  document.getElementById('high-count').textContent = summaryConfig.high.value.toLocaleString();
  document.getElementById('low-percent').textContent = summaryConfig.low.percent;
  document.getElementById('medium-percent').textContent = summaryConfig.medium.percent;
  document.getElementById('high-percent').textContent = summaryConfig.high.percent;
}

function showLoadingState() {
  elements.loading.classList.remove('hidden');
  elements.empty.classList.add('hidden');
  elements.error.classList.add('hidden');
  elements.tableBody.innerHTML = '';
}

function getFilteredRows() {
  const safeSearch = state.search.toLowerCase();

  let rows = studentRows.filter((student) => {
    const matchesSearch = student.studentId.toLowerCase().includes(safeSearch);
    const matchesPerformance = state.filters.performance === 'All' || student.performanceLevel === state.filters.performance;
    const matchesGender = state.filters.gender === 'All' || student.gender === state.filters.gender;
    const matchesSchool = state.filters.schoolType === 'All' || student.schoolType === state.filters.schoolType;
    const matchesModel = state.filters.model === 'All' || student.predictionModel === state.filters.model;

    return matchesSearch && matchesPerformance && matchesGender && matchesSchool && matchesModel;
  });

  if (state.sortBy) {
    rows = [...rows].sort((a, b) => {
      let left = a[state.sortBy];
      let right = b[state.sortBy];

      if (state.sortBy === 'performanceLevel') {
        const order = { Low: 1, Medium: 2, High: 3 };
        left = order[left] ?? 0;
        right = order[right] ?? 0;
      } else if (typeof left === 'string') {
        left = left.toLowerCase();
        right = right.toLowerCase();
      }

      if (left < right) return state.sortDirection === 'asc' ? -1 : 1;
      if (left > right) return state.sortDirection === 'asc' ? 1 : -1;
      return 0;
    });
  }

  return rows;
}

function renderTable() {
  const filteredRows = getFilteredRows();
  const totalPages = Math.max(1, Math.ceil(filteredRows.length / state.rowsPerPage));

  if (state.currentPage > totalPages) {
    state.currentPage = totalPages;
  }

  const startIndex = (state.currentPage - 1) * state.rowsPerPage;
  const pageRows = filteredRows.slice(startIndex, startIndex + state.rowsPerPage);

  elements.loading.classList.add('hidden');
  elements.error.classList.add('hidden');
  elements.totalCount.textContent = filteredRows.length.toLocaleString();
  elements.rowsCount.textContent = pageRows.length.toLocaleString();

  if (!filteredRows.length) {
    elements.tableBody.innerHTML = '';
    elements.empty.classList.remove('hidden');
    renderPagination(0);
    return;
  }

  elements.empty.classList.add('hidden');

  elements.tableBody.innerHTML = pageRows.map((student) => `
    <tr data-student-id="${student.studentId}">
      <td>${student.studentId}</td>
      <td>${student.gender}</td>
      <td>${student.schoolType}</td>
      <td>${Number(student.hoursStudied).toFixed(1)}</td>
      <td>${student.attendance}%</td>
      <td>${student.previousScores}</td>
      <td>${student.tutoringSessions}</td>
      <td>${Number(student.sleepHours).toFixed(1)}</td>
      <td>${student.physicalActivity}</td>
      <td><span class="performance-badge ${student.performanceLevel.toLowerCase()}">${student.performanceLevel}</span></td>
      <td>${student.predictionModel}</td>
    </tr>
  `).join('');

  document.querySelectorAll('#students-table-body tr').forEach((row) => {
    row.addEventListener('click', () => {
      const student = studentRows.find((item) => item.studentId === row.dataset.studentId);
      if (student) {
        openModal(student);
      }
    });
  });

  renderPagination(totalPages);
}

function renderPagination(totalPages) {
  const pagination = [];
  pagination.push(`<button type="button" class="page-btn ${state.currentPage === 1 ? 'disabled' : ''}" data-page="prev">Prev</button>`);

  for (let page = 1; page <= totalPages; page += 1) {
    pagination.push(`<button type="button" class="page-btn ${page === state.currentPage ? 'active' : ''}" data-page="${page}">${page}</button>`);
  }

  pagination.push(`<button type="button" class="page-btn ${state.currentPage === totalPages ? 'disabled' : ''}" data-page="next">Next</button>`);

  elements.pagination.innerHTML = pagination.join('');

  elements.pagination.querySelectorAll('.page-btn').forEach((button) => {
    button.addEventListener('click', () => {
      const targetPage = button.dataset.page;
      if (targetPage === 'prev') {
        if (state.currentPage > 1) {
          state.currentPage -= 1;
          renderTable();
        }
        return;
      }

      if (targetPage === 'next') {
        if (state.currentPage < totalPages) {
          state.currentPage += 1;
          renderTable();
        }
        return;
      }

      const page = Number(targetPage);
      if (!Number.isNaN(page)) {
        state.currentPage = page;
        renderTable();
      }
    });
  });
}

function openModal(student) {
  const sections = [
    {
      title: 'Student Information',
      rows: [
        ['Student ID', student.studentId],
        ['Gender', student.gender],
        ['School Type', student.schoolType]
      ]
    },
    {
      title: 'Academic Factors',
      rows: [
        ['Hours Studied', `${Number(student.hoursStudied).toFixed(1)} hrs`],
        ['Attendance', `${student.attendance}%`],
        ['Previous Scores', `${student.previousScores}`],
        ['Tutoring Sessions', `${student.tutoringSessions}`],
        ['Sleep Hours', `${Number(student.sleepHours).toFixed(1)} hrs`],
        ['Physical Activity', `${student.physicalActivity}`]
      ]
    },
    {
      title: 'Educational Factors',
      rows: [
        ['Parental Involvement', student.parentalInvolvement],
        ['Access to Resources', student.accessToResources],
        ['Motivation Level', student.motivationLevel],
        ['Internet Access', student.internetAccess],
        ['Teacher Quality', student.teacherQuality],
        ['Extracurricular Activities', student.extracurricularActivities]
      ]
    },
    {
      title: 'Other Factors',
      rows: [
        ['Family Income', student.familyIncome],
        ['Peer Influence', student.peerInfluence],
        ['Learning Disabilities', student.learningDisabilities],
        ['Parental Education Level', student.parentalEducationLevel],
        ['Distance from Home', `${student.distanceFromHome} km`]
      ]
    },
    {
      title: 'Prediction Result',
      rows: [
        ['Performance Level', `<span class="performance-badge ${student.performanceLevel.toLowerCase()}">${student.performanceLevel}</span>`],
        ['Model Used', student.predictionModel],
        ['Prediction Date/Time', student.predictionDateTime || 'Not available']
      ]
    }
  ];

  const html = `
    <div class="modal-header">
      <h3 id="modal-title">${student.studentId}</h3>
      <p class="modal-subhead">Academic performance record and prediction data</p>
    </div>
    <div class="modal-body">
      <div class="detail-grid">
        ${sections.map((section) => `
          <div class="detail-block">
            <h4>${section.title}</h4>
            <ul class="detail-list">
              ${section.rows.map(([label, value]) => `
                <li>
                  <span>${label}</span>
                  <span>${value}</span>
                </li>
              `).join('')}
            </ul>
          </div>
        `).join('')}
      </div>
    </div>
  `;

  elements.modalContent.innerHTML = html;
  elements.modal.classList.remove('hidden');
  elements.modal.setAttribute('aria-hidden', 'false');
}

function closeModal() {
  elements.modal.classList.add('hidden');
  elements.modal.setAttribute('aria-hidden', 'true');
}
