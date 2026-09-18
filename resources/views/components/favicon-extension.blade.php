<?php

if (!function_exists('localIcon')) {
    function localIcon($id)
    {
        $directory = base_path("assets/favicon/icons");
        // The icons dir is gitignored (upstream populates it at runtime) and
        // may not exist on a fresh deploy - scandir would fatal the page.
        $files = @scandir($directory);
        if ($files === false) {
            return "error.error";
        }
        $pathinfo = "error.error";
        foreach ($files as $file) {
            if (strpos($file, $id . '.') !== false) {
                $pathinfo = $id . "." . pathinfo($file, PATHINFO_EXTENSION);
            }
        }
        return $pathinfo;
    }
}
  
?>
