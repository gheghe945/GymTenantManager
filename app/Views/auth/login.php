<?php
    $bodyClass = 'login-page';
    $hideSidebar = true;
?>

<div class="login-container">
    <div class="login-logo">
        <h1><i class="fas fa-dumbbell"></i> GymManager</h1>
    </div>
    
    <div class="login-form">
        <h2 class="login-title">Sign In</h2>
        
        <form action="<?= URLROOT ?>/login" method="post" data-validate>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" class="form-control <?= !empty($email_err) ? 'is-invalid' : '' ?>" value="<?= $email ?>" required>
                <?php if (!empty($email_err)): ?>
                    <div class="invalid-feedback"><?= $email_err ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" class="form-control <?= !empty($password_err) ? 'is-invalid' : '' ?>" required>
                <?php if (!empty($password_err)): ?>
                    <div class="invalid-feedback"><?= $password_err ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary w-100">Login</button>
            </div>
        </form>
    </div>
</div>
