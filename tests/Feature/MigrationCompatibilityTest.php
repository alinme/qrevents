<?php

test('event asset comment likes migration uses a mysql-safe unique index name', function () {
    $migration = file_get_contents(database_path('migrations/2026_03_13_091000_create_event_asset_comment_likes_table.php'));

    expect($migration)->toContain("'eacl_comment_guest_unique'")
        ->and(strlen('eacl_comment_guest_unique'))->toBeLessThanOrEqual(64);
});
