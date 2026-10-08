<?php

return [
    // Argon2id supports the contract's full 12–128 character password range.
    'driver' => 'argon2id',
    'argon' => ['memory' => 65536, 'threads' => 1, 'time' => 4, 'verify' => true],
    'rehash_on_login' => true,
];
