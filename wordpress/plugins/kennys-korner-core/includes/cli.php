<?php

if (!defined('WP_CLI') || !WP_CLI) {
    return;
}

class KK_CLI {
    /**
     * Import demo pages, settings, and seed media.
     *
     * ## OPTIONS
     *
     * [--overwrite]
     * : Replace content previously created by this plugin.
     *
     * [--seed=<path>]
     * : Folder that contains images, menu, and videos.
     *
     * @when after_wp_load
     */
    public function import(array $args, array $assoc_args): void {
        $seed = isset($assoc_args['seed']) ? (string) $assoc_args['seed'] : '';
        if ($seed !== '') {
            add_filter('kk_seed_directory', static function () use ($seed) {
                return $seed;
            });
        }
        $result = KK_Importer::run_all(!empty($assoc_args['overwrite']));
        foreach ($result['log'] as $line) {
            WP_CLI::log($line);
        }
        if ($result['errors'] !== []) {
            foreach ($result['errors'] as $error) {
                WP_CLI::warning($error);
            }
            WP_CLI::error('Import finished with errors.');
        }
        WP_CLI::success('Import finished.');
    }
}
