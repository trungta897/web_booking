<!DOCTYPE html>
<html>
<head>
    <title>Debug Upload</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-2xl mx-auto bg-white p-6 rounded-lg shadow">
        <h1 class="text-xl font-bold mb-4">Debug Avatar Upload</h1>

        <!-- Current User Info -->
        <div class="mb-6 p-4 bg-gray-50 rounded">
            <h2 class="font-semibold mb-2">Current User & System Info</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p><strong>ID:</strong> {{ Auth::user()->id }}</p>
                    <p><strong>Name:</strong> {{ Auth::user()->name }}</p>
                    <p><strong>Email:</strong> {{ Auth::user()->email }}</p>
                    <p><strong>Role:</strong> {{ Auth::user()->role }}</p>
                    <p><strong>Avatar:</strong> {{ Auth::user()->avatar ?? 'None' }}</p>
                    @if(Auth::user()->avatar)
                        <p><strong>File Exists:</strong> {{ \Illuminate\Support\Facades\Storage::exists('public/avatars/' . Auth::user()->avatar) ? 'Yes' : 'No' }}</p>
                        <button onclick="clearAvatar()" class="mt-2 bg-red-500 text-white px-3 py-1 rounded text-sm hover:bg-red-600">
                            Clear Invalid Avatar
                        </button>
                    @endif
                </div>
                <div>
                    <p><strong>PHP Version:</strong> {{ PHP_VERSION }}</p>
                    <p><strong>Laravel Version:</strong> {{ app()->version() }}</p>
                    <p><strong>Environment:</strong> {{ app()->environment() }}</p>
                    <p><strong>Storage Link:</strong> {{ is_link(public_path('storage')) ? 'Exists' : 'Missing' }}</p>
                    <p><strong>CSRF Token:</strong> <span class="text-xs font-mono">{{ csrf_token() }}</span></p>
                    <p><strong>Upload Max Size:</strong> {{ ini_get('upload_max_filesize') }}</p>
                    <p><strong>Post Max Size:</strong> {{ ini_get('post_max_size') }}</p>
                </div>
            </div>
        </div>

        <form id="debugForm" enctype="multipart/form-data">
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Select Avatar</label>
                <input type="file" name="avatar" accept="image/*" class="block w-full border rounded px-3 py-2">
            </div>
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 mr-2">
                Test Upload
            </button>
            <button type="button" onclick="testPost()" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                Test POST
            </button>
        </form>

        <div id="result" class="mt-6 hidden">
            <h2 class="font-bold mb-2">Result:</h2>
            <pre id="resultContent" class="bg-gray-100 p-3 rounded text-sm overflow-x-auto max-h-96"></pre>
        </div>

        <div id="error" class="mt-4 hidden p-4 bg-red-50 border border-red-200 rounded">
            <h3 class="font-bold text-red-800 mb-2">Error Details:</h3>
            <p id="errorContent" class="text-red-700 text-sm"></p>
        </div>

        <!-- Quick Links -->
        <div class="mt-6 pt-4 border-t border-gray-200">
            <h3 class="font-semibold mb-2">Quick Debug Links:</h3>
            <div class="space-x-4 text-sm">
                <a href="/debug-avatar" target="_blank" class="text-blue-600 hover:underline">Debug Avatar</a>
                <a href="/debug-education" target="_blank" class="text-blue-600 hover:underline">Debug Education</a>
                <a href="/profile" class="text-blue-600 hover:underline">View Profile</a>
                <button onclick="location.reload()" class="text-green-600 hover:underline">Refresh Page</button>
            </div>
        </div>
    </div>

    <script>
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
    </script>
</body>
</html>
