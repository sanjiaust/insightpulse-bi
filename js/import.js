const els = {
  form: document.getElementById('uploadForm'),
  fileInput: document.getElementById('csvFile'),
  dropzone: document.getElementById('dropzone'),
  fileNameLabel: document.getElementById('fileNameLabel'),
  uploadBtn: document.getElementById('uploadBtn'),
  uploadMessage: document.getElementById('uploadMessage'),
};

function redirectIfUnauthorized(res) {
  if (res.status === 401) {
    window.location.href = 'login.php';
    return true;
  }
  return false;
}

els.fileInput.addEventListener('change', () => {
  els.fileNameLabel.textContent = els.fileInput.files.length ? els.fileInput.files[0].name : '';
});

['dragover', 'dragleave', 'drop'].forEach(evt => {
  els.dropzone.addEventListener(evt, e => {
    e.preventDefault();
    if (evt === 'dragover') els.dropzone.classList.add('drag-over');
    if (evt === 'dragleave' || evt === 'drop') els.dropzone.classList.remove('drag-over');
    if (evt === 'drop' && e.dataTransfer.files.length) {
      els.fileInput.files = e.dataTransfer.files;
      els.fileNameLabel.textContent = e.dataTransfer.files[0].name;
    }
  });
});

els.form.addEventListener('submit', async (e) => {
  e.preventDefault();
  if (!els.fileInput.files.length) {
    showUploadMessage('Please choose a CSV file first.', 'error');
    return;
  }

  const file = els.fileInput.files[0];
  if (!file.name.toLowerCase().endsWith('.csv')) {
    showUploadMessage('Invalid file format. Please upload a .csv file.', 'error');
    return;
  }

  const formData = new FormData();
  formData.append('csv_file', file);

  els.uploadBtn.disabled = true;
  els.uploadBtn.textContent = 'Importing…';

  try {
    const res = await fetch('upload.php', { method: 'POST', body: formData });
    if (redirectIfUnauthorized(res)) return;
    const data = await res.json();

    if (data.success) {
      let msg = data.message;
      if (data.skip_reasons && Object.keys(data.skip_reasons).length) {
        const reasons = Object.entries(data.skip_reasons).map(([reason, count]) => `${count} × ${reason}`).join('; ');
        msg += ` (${reasons})`;
      }
      showUploadMessage(msg, 'success');
      els.form.reset();
      els.fileNameLabel.textContent = '';
    } else {
      showUploadMessage(data.message, 'error');
    }
  } catch (err) {
    showUploadMessage('Upload failed: ' + err.message, 'error');
  } finally {
    els.uploadBtn.disabled = false;
    els.uploadBtn.textContent = 'Import CSV';
  }
});

function showUploadMessage(msg, type) {
  els.uploadMessage.textContent = msg;
  els.uploadMessage.className = 'upload-message ' + type;
  els.uploadMessage.hidden = false;
}
