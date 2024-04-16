<?php


if (!function_exists('includeRouteFiles')) {

    /**
     * Loops through a folder and requires all PHP files
     * Searches sub-directories as well.
     *
     * @param $folder
     */
    function includeRouteFiles($folder)
    {

        try {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    $folder,
                    FilesystemIterator::KEY_AS_PATHNAME | FilesystemIterator::CURRENT_AS_SELF | FilesystemIterator::SKIP_DOTS
                )
            );

            while ($files->valid()) {
                require $files->key();
                $files->next();
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}

if (!function_exists('getexcelcolumnname')) {
    function getexcelcolumnname($index)
    {
        //Get the quotient : if the index superior to base 26 max ?
        $quotient = $index / 26;
        if ($quotient >= 1) {
            //If yes, get top level column + the current column code
            return getexcelcolumnname($quotient - 1) . chr(($index % 26) + 65);
        } else {
            //If no just return the current column code
            return chr(65 + $index);
        }
    }
}
