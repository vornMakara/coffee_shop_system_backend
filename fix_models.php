<?php
$modelsPath = __DIR__ . '/app/Modules';
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($modelsPath));

foreach ($files as $file) {
    if ($file->isFile() && $file->getExtension() === 'php' && strpos($file->getPathname(), 'Models') !== false) {
        $content = file_get_contents($file->getPathname());
        
        // Find protected $fillable = [ ... ]; and replace with protected $guarded = ['id'];
        $pattern = '/protected\s+\$fillable\s*=\s*\[.*?\];/s';
        if (preg_match($pattern, $content)) {
            $newContent = preg_replace($pattern, 'protected $guarded = [\'id\'];', $content);
            file_put_contents($file->getPathname(), $newContent);
            echo "Updated " . $file->getFilename() . "\n";
        }
    }
}
