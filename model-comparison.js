const metricData = [
  { label: 'Accuracy', decisionTree: 72.39, randomForest: 82.22 },
  { label: 'Weighted Precision', decisionTree: 72.45, randomForest: 85.02 },
  { label: 'Weighted Recall', decisionTree: 72.39, randomForest: 82.22 },
  { label: 'Weighted F1-Score', decisionTree: 72.39, randomForest: 81.56 }
];

const confusionMatrices = [
  {
    model: 'Decision Tree', shortName: 'DT', theme: 'tree-mark', color: [52, 120, 229],
    rows: [[197, 90, 4], [79, 523, 104], [3, 85, 237]]
  },
  {
    model: 'Random Forest', shortName: 'RF', theme: 'forest-mark', color: [16, 166, 117],
    rows: [[173, 118, 0], [10, 687, 9], [1, 97, 227]]
  }
];

const featureImportance = [
  { feature: 'Attendance', value: 0.256158 },
  { feature: 'Hours Studied', value: 0.164922 },
  { feature: 'Previous Scores', value: 0.080975 },
  { feature: 'Tutoring Sessions', value: 0.040811 },
  { feature: 'Sleep Hours', value: 0.039703 },
  { feature: 'Physical Activity', value: 0.034276 },
  { feature: 'Access to Resources – High', value: 0.015773 },
  { feature: 'Parental Involvement – High', value: 0.014487 },
  { feature: 'Family Income – Low', value: 0.013737 },
  { feature: 'Peer Influence – Positive', value: 0.013707 }
];

document.addEventListener('DOMContentLoaded', () => {
  renderPerformanceChart();
  renderConfusionMatrices();
  renderFeatureImportance();
});

function renderPerformanceChart() {
  const svg = document.getElementById('performance-chart');
  const chartLeft = 64;
  const chartRight = 804;
  const chartTop = 28;
  const chartBottom = 275;
  const chartHeight = chartBottom - chartTop;
  const groupWidth = (chartRight - chartLeft) / metricData.length;
  const barWidth = 34;
  const barGap = 8;
  const parts = [];

  for (let tick = 0; tick <= 100; tick += 20) {
    const y = chartBottom - (tick / 100) * chartHeight;
    parts.push(`<line x1="${chartLeft}" y1="${y}" x2="${chartRight}" y2="${y}" stroke="#e9eef4" stroke-width="1"/>`);
    parts.push(`<text x="${chartLeft - 12}" y="${y + 4}" text-anchor="end" fill="#788b9d" font-size="11" font-family="Inter, sans-serif">${tick}</text>`);
  }

  parts.push(`<text x="17" y="${(chartTop + chartBottom) / 2}" transform="rotate(-90 17 ${(chartTop + chartBottom) / 2})" text-anchor="middle" fill="#657b8f" font-size="11" font-family="Inter, sans-serif">Score (%)</text>`);

  metricData.forEach((metric, index) => {
    const center = chartLeft + groupWidth * (index + 0.5);
    const treeX = center - barWidth - (barGap / 2);
    const forestX = center + (barGap / 2);
    const treeHeight = (metric.decisionTree / 100) * chartHeight;
    const forestHeight = (metric.randomForest / 100) * chartHeight;
    const treeY = chartBottom - treeHeight;
    const forestY = chartBottom - forestHeight;

    parts.push(`<rect x="${treeX}" y="${treeY}" width="${barWidth}" height="${treeHeight}" rx="5" fill="#3478e5"><title>Decision Tree ${metric.label}: ${metric.decisionTree.toFixed(2)}%</title></rect>`);
    parts.push(`<rect x="${forestX}" y="${forestY}" width="${barWidth}" height="${forestHeight}" rx="5" fill="#10a675"><title>Random Forest ${metric.label}: ${metric.randomForest.toFixed(2)}%</title></rect>`);
    parts.push(`<text x="${treeX + barWidth / 2}" y="${treeY - 7}" text-anchor="middle" fill="#315f9e" font-size="9" font-weight="700" font-family="Inter, sans-serif">${metric.decisionTree.toFixed(2)}</text>`);
    parts.push(`<text x="${forestX + barWidth / 2}" y="${forestY - 7}" text-anchor="middle" fill="#07835a" font-size="9" font-weight="700" font-family="Inter, sans-serif">${metric.randomForest.toFixed(2)}</text>`);
    parts.push(`<text x="${center}" y="${chartBottom + 22}" text-anchor="middle" fill="#61778b" font-size="10" font-family="Inter, sans-serif">${metric.label.replace(' ', '\u00a0')}</text>`);
  });

  svg.insertAdjacentHTML('beforeend', parts.join(''));
}

function renderConfusionMatrices() {
  const labels = ['Low', 'Medium', 'High'];
  const container = document.getElementById('confusion-matrices');

  container.innerHTML = confusionMatrices.map((matrix) => {
    const maxValue = Math.max(...matrix.rows.flat());
    const body = matrix.rows.map((row, rowIndex) => `
      <tr>
        <th scope="row">${labels[rowIndex]}</th>
        ${row.map((value, columnIndex) => {
          const alpha = 0.08 + (value / maxValue) * 0.82;
          const [red, green, blue] = matrix.color;
          const diagonal = rowIndex === columnIndex;
          const background = diagonal ? `rgba(${red}, ${green}, ${blue}, ${Math.max(alpha, 0.72)})` : `rgba(${red}, ${green}, ${blue}, ${alpha})`;
          return `<td class="${diagonal ? 'diagonal' : ''}" style="background-color:${background}" aria-label="Actual ${labels[rowIndex]}, predicted ${labels[columnIndex]}: ${value}">${value}</td>`;
        }).join('')}
      </tr>
    `).join('');

    return `
      <article class="matrix-card">
        <h3><span class="model-mark ${matrix.theme}" aria-hidden="true">${matrix.shortName}</span>${matrix.model}</h3>
        <p class="matrix-axis">Predicted</p>
        <table class="matrix-table" aria-label="${matrix.model} confusion matrix, Actual vs Predicted">
          <thead><tr><th scope="col">Actual</th>${labels.map((label) => `<th scope="col">${label}</th>`).join('')}</tr></thead>
          <tbody>${body}</tbody>
        </table>
      </article>
    `;
  }).join('');
}

function renderFeatureImportance() {
  const container = document.getElementById('feature-importance-list');
  const maxValue = Math.max(...featureImportance.map((item) => item.value));

  container.innerHTML = featureImportance.map((item, index) => `
    <div class="feature-row">
      <span class="feature-name"><strong>${String(index + 1).padStart(2, '0')}.</strong> ${item.feature}</span>
      <span class="feature-track" role="img" aria-label="${item.feature}: ${item.value.toFixed(6)} importance">
        <span class="feature-fill" style="width:${(item.value / maxValue) * 100}%"></span>
      </span>
      <span class="feature-value">${item.value.toFixed(6)}</span>
    </div>
  `).join('');
}
