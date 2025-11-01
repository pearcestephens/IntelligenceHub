# Frontend Development Guide

## HTML5 & Semantic Markup

### Best Practices
```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Semantic HTML Example</title>
</head>
<body>
    <header>
        <nav aria-label="Main navigation">
            <ul>
                <li><a href="#home">Home</a></li>
                <li><a href="#about">About</a></li>
            </ul>
        </nav>
    </header>
    
    <main>
        <article>
            <h1>Article Title</h1>
            <section>
                <h2>Section Heading</h2>
                <p>Content goes here...</p>
            </section>
        </article>
    </main>
    
    <footer>
        <p>&copy; 2025 Company Name</p>
    </footer>
</body>
</html>
```

## CSS3 & Modern Styling

### Flexbox Layout
```css
.container {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    padding: 1rem;
}

.header {
    flex: 0 0 auto;
}

.main {
    flex: 1 1 auto;
}

.footer {
    flex: 0 0 auto;
}
```

### CSS Grid
```css
.grid-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    padding: 2rem;
}

.grid-item {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}
```

## JavaScript ES6+ Fundamentals

### Modern Syntax
```javascript
// Destructuring
const { name, email } = user;
const [first, second] = items;

// Template Literals
const message = \`Hello, ${name}! You have ${count} messages.\`;

// Arrow Functions
const processData = (data) => data.map(item => ({
    ...item,
    processed: true
}));

// Async/Await
const fetchUserData = async (userId) => {
    try {
        const response = await fetch(\`/api/users/${userId}\`);
        const userData = await response.json();
        return userData;
    } catch (error) {
        console.error('Error fetching user data:', error);
        throw error;
    }
};
```

## React Development

### Component Best Practices
```jsx
import React, { useState, useEffect, useMemo } from 'react';

const UserProfile = ({ userId }) => {
    const [user, setUser] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    
    useEffect(() => {
        const fetchUser = async () => {
            try {
                setLoading(true);
                const userData = await fetchUserData(userId);
                setUser(userData);
            } catch (err) {
                setError(err.message);
            } finally {
                setLoading(false);
            }
        };
        
        fetchUser();
    }, [userId]);
    
    const displayName = useMemo(() => {
        return user ? \`${user.firstName} ${user.lastName}\` : '';
    }, [user]);
    
    if (loading) return <div>Loading...</div>;
    if (error) return <div>Error: {error}</div>;
    if (!user) return <div>User not found</div>;
    
    return (
        <div className="user-profile">
            <h2>{displayName}</h2>
            <p>{user.email}</p>
        </div>
    );
};

export default UserProfile;
```
