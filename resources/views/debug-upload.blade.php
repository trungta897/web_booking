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

    
</body>
</html>

    @push('scripts')
        <script src="{{ asset('js/pages/debug-upload.js') }}"></script>
    @endpush