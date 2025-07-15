/**
 * Extracted from: debug-upload.blade.php
 * Generated on: 2025-07-15 03:51:34
 */

document.addEventListener('DOMContentLoaded', function() {
async function clearAvatar() {
            if (!confirm('Are you sure you want to clear the avatar from database?')) {
                return;
            }

            try {
                const response = await fetch('/clear-avatar', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                const data = await response.json();
                alert(data.message || 'Avatar cleared successfully');
                location.reload();
            } catch (error) {
                alert('Error: ' + error.message);
            }
        }

        async function testPost() {
            const resultDiv = document.getElementById('result');
            const resultContent = document.getElementById('resultContent');
            const errorDiv = document.getElementById('error');
            const errorContent = document.getElementById('errorContent');

            // Hide previous results
            resultDiv.classList.add('hidden');
            errorDiv.classList.add('hidden');

            try {
                console.log('Testing simple POST request...');

                const response = await fetch('/test-post', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        test: 'data',
                        timestamp: new Date().toISOString()
                    })
                });

                console.log('Test POST response status:', response.status);

                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const text = await response.text();
                    console.error('Non-JSON response from test-post:', text);

                    errorContent.innerHTML = `
                        <strong>Test POST failed - Non-JSON response</strong><br>
                        Status: ${response.status}<br>
                        Content-Type: ${contentType}<br>
                        Response: ${text.substring(0, 300)}...
                    `;
                    errorDiv.classList.remove('hidden');
                    return;
                }

                const data = await response.json();
                console.log('Test POST success:', data);

                resultContent.textContent = 'TEST POST SUCCESS:\n\n' + JSON.stringify(data, null, 2);
                resultDiv.classList.remove('hidden');

            } catch (error) {
                console.error('Test POST error:', error);

                errorContent.innerHTML = `
                    <strong>Test POST Error:</strong><br>
                    ${error.message}
                `;
                errorDiv.classList.remove('hidden');
            }
        }

        document.getElementById('debugForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const resultDiv = document.getElementById('result');
            const resultContent = document.getElementById('resultContent');
            const errorDiv = document.getElementById('error');
            const errorContent = document.getElementById('errorContent');

            // Hide previous results
            resultDiv.classList.add('hidden');
            errorDiv.classList.add('hidden');

            // Show loading state
            resultContent.textContent = 'Uploading...';
            resultDiv.classList.remove('hidden');

            try {
                console.log('Sending request to /debug-upload');

                const response = await fetch('/debug-upload', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);

                // Check if response is JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const text = await response.text();
                    console.error('Non-JSON response:', text);

                    errorContent.innerHTML = `
                        <strong>Server returned HTML instead of JSON</strong><br>
                        Status: ${response.status}<br>
                        Content-Type: ${contentType}<br>
                        Response preview: ${text.substring(0, 200)}...
                    `;
                    errorDiv.classList.remove('hidden');
                    resultDiv.classList.add('hidden');
                    return;
                }

                const data = await response.json();
                console.log('Response data:', data);

                resultContent.textContent = JSON.stringify(data, null, 2);
                resultDiv.classList.remove('hidden');

                // Auto reload if upload successful
                if (data.upload_result && data.upload_result.success) {
                    setTimeout(() => {
                        console.log('Upload successful, reloading page...');
                        location.reload();
                    }, 3000);
                }
            } catch (error) {
                console.error('Fetch error:', error);

                errorContent.innerHTML = `
                    <strong>JavaScript Error:</strong><br>
                    ${error.message}<br>
                    <br>
                    <strong>Possible causes:</strong><br>
                    • CSRF token mismatch<br>
                    • Server returned HTML error page<br>
                    • Network connection issue<br>
                    • Middleware blocking the request
                `;
                errorDiv.classList.remove('hidden');
                resultDiv.classList.add('hidden');
            }
        });
});