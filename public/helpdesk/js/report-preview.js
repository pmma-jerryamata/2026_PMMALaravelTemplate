const previewForm = document.getElementById('accomplishment-report-form');
const previewButton = document.querySelector('[data-report-preview]');
if (previewForm && previewButton && previewForm.checkValidity()) previewForm.requestSubmit(previewButton);
