<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Daily Active Salary Table</title>
  <style>
    body{
      font-family: Arial, sans-serif;
      background-color: #f4f6f9;
      padding: 20px;
      display: flex;
      flex-direction: column;
      align-items: center;
    }
    h2 {
      margin-bottom: 20px;
    }
    .table-container {
      width: 100%;
      max-width: 800px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      background-color: #ffffff;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    th, td {
      padding: 12px;
      text-align: center;
      border: 1px solid #ddd;
    }
    th {
      background-color: #007BFF;
      color: white;
    }
    tr:nth-child(even) {
      background-color: #f9f9f9;
    }
    tr:hover {
      background-color: #f1f1f1;
    }
  </style>
</head>
<body>

  <h2>Daily Active - Deposit - Salary Chart</h2>

  <div class="table-container">
    <table>
      <thead>
        <tr>
          <th>Daily Active</th>
          <th>Deposit</th>
          <th>Salary</th>
        </tr>
      </thead>
      <tbody>
        <tr><td>3 & above</td><td>1K</td><td>150</td></tr>
        <tr><td>5 & above</td><td>3K</td><td>300</td></tr>
        <tr><td>5 & above</td><td>10K</td><td>600</td></tr>
        <tr><td>10 & above</td><td>20K</td><td>1200</td></tr>
        <tr><td>10 & above</td><td>40K</td><td>2K</td></tr>
        <tr><td>10 & above</td><td>70K</td><td>3K</td></tr>
        <tr><td>20 & above</td><td>120K</td><td>5K</td></tr>
        <tr><td>20 & above</td><td>200K</td><td>8K</td></tr>
        <tr><td>20 & above</td><td>250K</td><td>10K</td></tr>
        <tr><td>20 & above</td><td>350K</td><td>15K</td></tr>
        <tr><td>30 & above</td><td>620K</td><td>25K</td></tr>
        <tr><td>30 & above</td><td>1250K</td><td>32K</td></tr>
        <tr><td>30 & above</td><td>3000K</td><td>40K</td></tr>
        <tr><td>30 & above</td><td>6000K</td><td>80K</td></tr>
        <tr><td>30 & above</td><td>18000K</td><td>100K</td></tr>
      </tbody>
    </table>
  </div>

</body>
</html>
