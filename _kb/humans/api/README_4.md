# Backend Development Guide

## PHP 8+ Best Practices

### Object-Oriented Programming
```php
<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Exceptions\UserNotFoundException;

class UserService
{
    public function __construct(
        private UserRepository $userRepository,
        private EmailService $emailService
    ) {}
    
    public function createUser(array $userData): User
    {
        $user = new User([
            'name' => $userData['name'],
            'email' => $userData['email'],
            'password' => password_hash($userData['password'], PASSWORD_ARGON2ID)
        ]);
        
        $savedUser = $this->userRepository->save($user);
        $this->emailService->sendWelcomeEmail($savedUser);
        
        return $savedUser;
    }
    
    public function getUserById(int $id): User
    {
        $user = $this->userRepository->findById($id);
        
        if (!$user) {
            throw new UserNotFoundException("User with ID {$id} not found");
        }
        
        return $user;
    }
}
```

### API Design
```php
<?php
// RESTful API Controller
class ApiController
{
    public function handleRequest(string $method, string $endpoint, array $data = []): array
    {
        try {
            $result = match($method) {
                'GET' => $this->handleGet($endpoint),
                'POST' => $this->handlePost($endpoint, $data),
                'PUT' => $this->handlePut($endpoint, $data),
                'DELETE' => $this->handleDelete($endpoint),
                default => throw new InvalidArgumentException('Unsupported method')
            };
            
            return [
                'success' => true,
                'data' => $result,
                'timestamp' => time()
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode()
                ],
                'timestamp' => time()
            ];
        }
    }
}
```

## Node.js Development

### Express.js Best Practices
```javascript
const express = require('express');
const helmet = require('helmet');
const rateLimit = require('express-rate-limit');

const app = express();

// Security middleware
app.use(helmet());
app.use(express.json({ limit: '10mb' }));

// Rate limiting
const limiter = rateLimit({
    windowMs: 15 * 60 * 1000, // 15 minutes
    max: 100 // limit each IP to 100 requests per windowMs
});
app.use(limiter);

// User routes
app.get('/api/users/:id', async (req, res) => {
    try {
        const userId = parseInt(req.params.id);
        const user = await userService.getUserById(userId);
        
        res.json({
            success: true,
            data: user
        });
    } catch (error) {
        res.status(error.status || 500).json({
            success: false,
            error: {
                message: error.message,
                code: error.code
            }
        });
    }
});

module.exports = app;
```
