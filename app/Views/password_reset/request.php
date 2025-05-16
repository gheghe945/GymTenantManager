<?php $title = 'Recupera Password - GymManager'; ?>

<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="text-center">Recupera Password</h4>
                </div>
                <div class="card-body">
                    <?php flash('password_reset_message'); ?>
                    
                    <p class="text-center mb-4">
                        Inserisci il tuo indirizzo email e ti invieremo un link per reimpostare la password.
                    </p>
                    
                    <form action="<?= URLROOT ?>/password/request" method="post">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" name="email" id="email" class="form-control" required>
                        </div>
                        
                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-paper-plane"></i> Invia Link di Reset
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