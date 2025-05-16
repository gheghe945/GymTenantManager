<?php $title = 'Reimposta Password - GymManager'; ?>

<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="text-center">Reimposta Password</h4>
                </div>
                <div class="card-body">
                    <?php flash('reset_message'); ?>
                    
                    <p class="text-center mb-4">
                        Inserisci una nuova password per il tuo account.
                    </p>
                    
                    <form action="<?= URLROOT ?>/password/do-reset" method="post">
                        <input type="hidden" name="token" value="<?= $token ?>">
                        <input type="hidden" name="email" value="<?= $email ?>">
                        
                        <div class="form-group">
                            <label for="password">Nuova Password</label>
                            <input type="password" name="password" id="password" class="form-control" required>
                            <small class="form-text text-muted">La password deve essere lunga almeno 6 caratteri.</small>
                        </div>
                        
                        <div class="form-group mt-3">
                            <label for="confirm_password">Conferma Password</label>
                            <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                        </div>
                        
                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-key"></i> Reimposta Password
                            </button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-3">
                        <a href="<?= URLROOT ?>/login" class="text-decoration-none">
                            <i class="fas fa-arrow-left"></i> Torna al Login
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>