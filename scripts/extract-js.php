<?php

/**
 * Script to extract JavaScript from Blade templates into separate files
 * Usage: php extract-js.php
 */

class BladeJavaScriptExtractor
{
    private $viewsPath;
    private $jsPath;
    private $extractedFiles = [];

    public function __construct()
    {
        $this->viewsPath = __DIR__ . '/../resources/views';
        $this->jsPath = __DIR__ . '/../public/js/pages';
        
        // Ensure JS directory exists
        if (!is_dir($this->jsPath)) {
            mkdir($this->jsPath, 0755, true);
        }
    }

    public function extractAll()
    {
        echo "Starting JavaScript extraction from Blade templates...\n";
        
        $bladeFiles = $this->findBladeFiles();
        
        foreach ($bladeFiles as $file) {
            $this->processFile($file);
        }
        
        $this->generateReport();
    }

    private function findBladeFiles()
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->viewsPath)
        );
        
        $bladeFiles = [];
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php' && 
                strpos($file->getFilename(), '.blade.php') !== false) {
                $bladeFiles[] = $file->getPathname();
            }
        }
        
        return $bladeFiles;
    }

    private function processFile($filePath)
    {
        $content = file_get_contents($filePath);
        $relativePath = str_replace($this->viewsPath . DIRECTORY_SEPARATOR, '', $filePath);
        
        // Find script tags with content
        preg_match_all('/<script[^>]*>(.*?)<\/script>/s', $content, $matches, PREG_OFFSET_CAPTURE);
        
        if (empty($matches[1])) {
            return;
        }

        $hasInlineJS = false;
        $jsContent = '';
        $newBladeContent = $content;
        
        // Process from last to first to maintain string positions
        for ($i = count($matches[0]) - 1; $i >= 0; $i--) {
            $fullMatch = $matches[0][$i][0];
            $jsCode = trim($matches[1][$i][0]);
            $offset = $matches[0][$i][1];
            
            // Skip empty scripts or external scripts
            if (empty($jsCode) || $this->isExternalScript($fullMatch)) {
                continue;
            }
            
            $hasInlineJS = true;
            
            // Add to JS content (prepend since we're going backwards)
            if (!empty($jsContent)) {
                $jsContent = $jsCode . "\n\n" . $jsContent;
            } else {
                $jsContent = $jsCode;
            }
            
            // Remove the script tag from blade content
            $newBladeContent = substr_replace($newBladeContent, '', $offset, strlen($fullMatch));
        }
        
        if ($hasInlineJS) {
            $this->saveExtractedJS($relativePath, $jsContent, $newBladeContent, $filePath);
        }
    }

    private function isExternalScript($scriptTag)
    {
        return strpos($scriptTag, 'src=') !== false;
    }

    private function saveExtractedJS($relativePath, $jsContent, $newBladeContent, $originalPath)
    {
        // Generate JS filename based on blade path
        $jsFileName = $this->generateJSFileName($relativePath);
        $jsFilePath = $this->jsPath . DIRECTORY_SEPARATOR . $jsFileName;
        
        // Wrap JS content in DOMContentLoaded if not already wrapped
        if (strpos($jsContent, 'DOMContentLoaded') === false) {
            $jsContent = "document.addEventListener('DOMContentLoaded', function() {\n" . $jsContent . "\n});";
        }
        
        // Add header comment
        $jsFileContent = "/**\n * Extracted from: {$relativePath}\n * Generated on: " . date('Y-m-d H:i:s') . "\n */\n\n" . $jsContent;
        
        // Save JS file
        file_put_contents($jsFilePath, $jsFileContent);
        
        // Add script include to blade content
        $scriptInclude = "\n    @push('scripts')\n        <script src=\"{{ asset('js/pages/{$jsFileName}') }}\"></script>\n    @endpush";
        
        // Try to add before closing tag, or append at end
        if (strpos($newBladeContent, '</x-app-layout>') !== false) {
            $newBladeContent = str_replace('</x-app-layout>', $scriptInclude . "\n</x-app-layout>", $newBladeContent);
        } elseif (strpos($newBladeContent, '@endsection') !== false) {
            $newBladeContent = str_replace('@endsection', $scriptInclude . "\n@endsection", $newBladeContent);
        } else {
            $newBladeContent .= $scriptInclude;
        }
        
        // Create backup of original file
        $backupPath = $originalPath . '.backup.' . date('Y-m-d-H-i-s');
        copy($originalPath, $backupPath);
        
        // Save updated blade file
        file_put_contents($originalPath, $newBladeContent);
        
        $this->extractedFiles[] = [
            'blade' => $relativePath,
            'js' => $jsFileName,
            'backup' => basename($backupPath)
        ];
        
        echo "✓ Extracted JS from: {$relativePath} -> {$jsFileName}\n";
    }

    private function generateJSFileName($bladePath)
    {
        // Convert blade path to JS filename
        $name = str_replace(['\\', '/', '.blade.php'], ['-', '-', ''], $bladePath);
        $name = preg_replace('/[^a-zA-Z0-9\-_]/', '-', $name);
        $name = preg_replace('/-+/', '-', $name);
        $name = trim($name, '-');
        
        return $name . '.js';
    }

    private function generateReport()
    {
        echo "\n" . str_repeat("=", 50) . "\n";
        echo "EXTRACTION COMPLETE\n";
        echo str_repeat("=", 50) . "\n";
        echo "Files processed: " . count($this->extractedFiles) . "\n\n";
        
        if (!empty($this->extractedFiles)) {
            echo "Extracted files:\n";
            foreach ($this->extractedFiles as $file) {
                echo "  • {$file['blade']} -> js/pages/{$file['js']}\n";
                echo "    Backup: {$file['backup']}\n\n";
            }
            
            echo "Next steps:\n";
            echo "1. Test your pages to ensure JavaScript works correctly\n";
            echo "2. If everything works, you can delete the .backup files\n";
            echo "3. Consider organizing JS files into subdirectories if needed\n";
        } else {
            echo "No inline JavaScript found in Blade templates.\n";
        }
    }
}

// Run the extractor
$extractor = new BladeJavaScriptExtractor();
$extractor->extractAll();