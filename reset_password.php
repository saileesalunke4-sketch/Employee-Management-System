<?php
require 'db.php';

$new_password = password_hash('Pass@123', PASSWORD_DEFAULT);

$query = "UPDATE users SET password='$new_password'";
mysqli_query($conn, $query);

echo "All passwords updated to Pass@123 successfully!";
?>
```

### Step 2 — Run it in browser
```
http://localhost/emp/reset_pass.php
```

You should see:
```
All passwords updated to Pass@123 successfully!
```

### Step 3 — Now login with
```
Password: Pass@123