@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Import Excel File</div>

                <div class="card-body">
                    <form id="importForm" enctype="multipart/form-data">
                        @csrf

                        <div class="form-group">
                            <label for="file">Excel File (xlsx)</label>
                            <input type="file" class="form-control-file" id="file" name="file" accept=".xlsx" required>
                            <small class="form-text text-muted">
                                File should have columns: id, name, date
                            </small>
                        </div>

                        <button type="submit" class="btn btn-primary">Import</button>
                    </form>

                    <div id="progressContainer" class="mt-4" style="display: none;">
                        <h5>Import Progress</h5>
                        <div class="progress">
                            <div id="progressBar" class="progress-bar" role="progressbar" style="width: 0%"></div>
                        </div>
                        <p id="progressText" class="mt-2">0%</p>

                        <div id="resultContainer" class="mt-3" style="display: none;">
                            <h5>Import Results</h5>
                            <div class="alert alert-success">
                                File imported successfully!
                            </div>
                            <div id="errorsContainer" class="mt-3" style="display: none;">
                                <h6>Errors found:</h6>
                                <pre id="errorContent" class="bg-light p-3"></pre>
                                <a href="/storage/result.txt" download class="btn btn-sm btn-outline-danger">
                                    Download Error Report
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('importForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData();
            formData.append('file', document.getElementById('file').files[0]);
            formData.append('_token', '{{ csrf_token() }}');

            document.getElementById('progressContainer').style.display = 'block';

            fetch('/api/import', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    const progressKey = data.progress_key;
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred during import');
                });
        });

        function showResults() {
            const resultContainer = document.getElementById('resultContainer');
            resultContainer.style.display = 'block';

            fetch('/storage/result.txt')
                .then(response => {
                    if (response.ok) {
                        return response.text();
                    }
                    return Promise.reject();
                })
                .then(text => {
                    if (text.trim().length > 0) {
                        document.getElementById('errorsContainer').style.display = 'block';
                        document.getElementById('errorContent').textContent = text;
                    }
                })
                .catch(() => {});
        }

        function updateProgress(processed, total) {
            const progressBar = document.getElementById('progressBar');
            const progressText = document.getElementById('progressText');

            const percentage = Math.round((processed / total) * 100);

            progressBar.style.width = `${percentage}%`;
            progressBar.setAttribute('aria-valuenow', percentage);
            progressText.textContent = `${percentage}% (${processed} of ${total} rows processed)`;

            if (processed === total) {
                showResults();
            }
        }

        window.Echo.channel('imports')
            .listen('.import.progress', (e) => {
                const progressData = e.progressData;
                updateProgress(progressData.processed, progressData.total);
            });
    });
</script>
@endsection