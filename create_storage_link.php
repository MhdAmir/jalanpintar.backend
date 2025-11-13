<?php
/**
 * Storage Link Creator for Shared Hosting
 * 
 * This script creates a symbolic link for Laravel storage
 * when php artisan storage:link doesn't work on shared hosting.
 * 
 * Upload this file to your root directory and run once:
 * php create_storage_link.php
 * 
 * Then DELETE this file for security.
 */

echo "=== Laravel Storage Link Creator ===\n\n";

// Adjust these paths according to your hosting structure
$target = __DIR__ . '/storage/app/public';
$link = __DIR__ . '/public_html/storage';  // Change 'public_html' to 'public' if needed

echo "Target: $target\n";
echo "Link: $link\n\n";

// Check if target exists
if (!file_exists($target)) {
    echo "❌ ERROR: Target directory does not exist: $target\n";
    echo "   Please make sure your storage directory structure is correct.\n";
    exit(1);
}

// Remove existing link/directory if exists
if (file_exists($link)) {
    echo "⚠ Existing storage link found. Removing...\n";
    if (is_link($link)) {
        unlink($link);
        echo "✓ Old symlink removed\n\n";
    } elseif (is_dir($link)) {
        // If it's a directory, try to remove it (only if empty)
        if (@rmdir($link)) {
            echo "✓ Old directory removed\n\n";
        } else {
            echo "❌ ERROR: Cannot remove existing directory. It's not empty.\n";
            echo "   Please manually remove or backup: $link\n";
            exit(1);
        }
    } else {
        unlink($link);
        echo "✓ Old file removed\n\n";
    }
}

// Try to create symlink
echo "Creating symbolic link...\n";

if (function_exists('symlink')) {
    if (@symlink($target, $link)) {
        echo "✅ SUCCESS! Storage link created successfully!\n\n";

        // Verify the link
        if (is_link($link)) {
            echo "Verification: Link exists and points to " . readlink($link) . "\n";
        }

        echo "\n📝 Next steps:\n";
        echo "1. Delete this script (create_storage_link.php) for security\n";
        echo "2. Test file uploads in your application\n";
        echo "3. Make sure storage/app/public is writable (chmod 755)\n";

    } else {
        echo "❌ ERROR: Failed to create symbolic link.\n";
        echo "   Possible reasons:\n";
        echo "   - PHP doesn't have permission to create symlinks\n";
        echo "   - The symlink function is disabled\n";
        echo "   - File system doesn't support symlinks\n\n";
        echo "   Please try manual creation via SSH:\n";
        echo "   cd public_html\n";
        echo "   ln -s ../storage/app/public storage\n\n";
        echo "   Or contact your hosting support.\n";
        exit(1);
    }
} else {
    echo "❌ ERROR: symlink() function is not available.\n";
    echo "   The PHP symlink function is disabled on this server.\n\n";
    echo "   Solutions:\n";
    echo "   1. Create link manually via SSH (recommended):\n";
    echo "      cd public_html\n";
    echo "      ln -s ../storage/app/public storage\n\n";
    echo "   2. Contact hosting support to enable symlink function\n";
    echo "   3. Use File Manager to create symbolic link\n";
    exit(1);
}
