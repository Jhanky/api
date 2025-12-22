$user = App\Models\User::updateOrCreate(
    ['email' => 'test@vatiocore.com'],
    [
        'name' => 'Test User',
        'password' => Hash::make('password123'),
        'username' => 'testuser',
        'is_active' => true
    ]
);
echo "User ID: " . $user->id;
exit;
