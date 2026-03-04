<?php
require('auth.php');
header('Expires: 0');
header('Content-type: application/json');

$xml = file_get_contents(el_s3_getTemporaryLink(ACCESSKEY, SECRETKEY, S3BUCKET, '/'), false);
$xml = simplexml_load_string($xml);

$files = array();
$tree = array();

// First pass: collect all files with their full paths
foreach ($xml->Contents as $Contents) {
    $f = (array) $Contents->Key;
    $name = $f[0];
    $size = (array) $Contents->Size;
    
    // Create a flat structure first to understand the data
    $files[] = array(
        "name" => $name,
        "type" => "file",
        "size" => $size[0]
    );
}

// Now build the hierarchical structure
function buildTree($files) {
    $root = array();
    
    foreach ($files as $file) {
        $pathParts = explode('/', trim($file['name'], '/'));
        $currentNode = &$root;
        
        // Navigate through path parts to create nested folders
        for ($i = 0; $i < count($pathParts); $i++) {
            $part = $pathParts[$i];
            
            // Check if this part already exists as a folder in current node
            $folderExists = false;
            foreach ($currentNode as &$node) {
                if ($node['name'] == $part && $node['type'] == 'folder') {
                    $currentNode = &$node['items'];
                    $folderExists = true;
                    break;
                }
            }
            
            // If folder doesn't exist, create it
            if (!$folderExists && $i < count($pathParts) - 1) {
                // This is a folder (not the final file)
                $currentNode[] = array(
                    'name' => $part,
                    'type' => 'folder',
                    'path' => 's3browser/'.implode('/', array_slice($pathParts, 0, $i + 1)),
                    'items' => array()
                );
                $currentNode = &$currentNode[count($currentNode) - 1]['items'];
            } else if ($i == count($pathParts) - 1 && !$folderExists) {
                // This is a file (final part)
                $currentNode[] = array(
                    'name' => $part,
                    'type' => 'file',
                    'path' => el_s3_getTemporaryLink(ACCESSKEY, SECRETKEY, S3BUCKET,implode('/', array_slice($pathParts, 0, $i + 1))),
                    'size' => $file['size']
                );
            }
        }
    }
    
    return $root;
}

// Build the tree structure
$tree = buildTree($files);

// Create final output with proper structure
$output = array(
    "name" => 's3browser',
    "type" => "folder",
    "path" => 's3browser',
    "filenum" => count($files),
    "items" => $tree
);

echo json_encode($output);
?>
