<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Options Page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .options-container {
            display: flex;
            flex-direction: column;
            gap: 40px; /* Increased space between options */
            width: 100%;
            max-width: 1200px; /* Further increased maximum width */
            padding: 40px; /* Increased padding */
            box-sizing: border-box;
        }
        .option {
            background: #fff;
            border-radius: 12px; /* Larger border-radius */
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.15); /* Larger shadow */
            padding: 40px; /* Increased padding */
            text-align: center;
            font-size: 20px; /* Larger font size */
        }
        .option h2 {
            margin-top: 0;
            color: #333;
            font-size: 28px; /* Larger heading size */
        }
        .option p {
            color: #666;
            font-size: 18px; /* Larger text size */
        }
        .option a {
            text-decoration: none;
            color: #007bff;
            font-weight: bold;
            font-size: 20px; /* Larger link size */
        }
        .option a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="options-container">
        <div class="option">
            <h2> approve content</h2>
            <p>clicl blow to approve content </p>
            <a href="approvel2.php" >Click here </a>
        </div>
        
        <div class="option">
            <h2>Option 2 Title</h2>
            <p>This is a brief description of Option 2. It explains what this option entails and why it might be useful.</p>
            <a href="https://example.com/option2" target="_blank">Click here for more details</a>
        </div>
        
        <div class="option">
            <h2>Option 3 Title</h2>
            <p>This is a brief description of Option 3. Learn more about this option and its benefits by clicking the link below.</p>
            <a href="https://example.com/option3" target="_blank">Click here for more details</a>
        </div>
    </div>
</body>
</html>
