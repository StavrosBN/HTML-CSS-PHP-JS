<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Date Range and Format</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 50px;
        }
        form {
            display: inline-block;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
            box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.1);
        }
        label {
            font-weight: bold;
        }
        input, select, button {
            margin: 10px 0;
            padding: 8px;
            font-size: 16px;
        }
        button {
            cursor: pointer;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
        }
        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

    <h2>Select Start Date, End Date, and Format</h2>
    
    <form id="dateForm" method="GET" action="redirect.php">
        <label for="start_date">Start Date:</label>
        <input type="date" id="start_date" name="start_date" required>
        <br>
        
        <label for="end_date">End Date:</label>
        <input type="date" id="end_date" name="end_date" required>
        <br>
        
        <label for="format">Select Format:</label>
        <select id="format" name="format">
            <option value="json">JSON</option>
            <option value="xml">XML</option>
        </select>
        <br>
        
        <button type="submit">Submit</button>
    </form>

</body>
</html>
