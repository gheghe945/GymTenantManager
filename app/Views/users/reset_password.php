<?php require APPROOT . '/Views/includes/header.php'; ?>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card card-body bg-light mt-5">
            <h2>Reset Password per <?= $data['user']['name'] ?></h2>
            <p>Compila questo form per reimpostare la password dell'utente</p>
            <form action="<?= URLROOT ?>/users/updatePassword/<?= $data['id'] ?>" method="post">
                <div class="form-group mb-3">
                    <label for="new_password">Nuova Password: <sup>*</sup></label>
                    <input type="password" name="new_password" class="form-control form-control-lg <?= (!empty($data['new_password_err'])) ? 'is-invalid' : ''; ?>" value="<?= $data['new_password']; ?>">
                    <span class="invalid-feedback"><?= $data['new_password_err']; ?></span>
                </div>
                <div class="form-group mb-3">
                    <label for="confirm_password">Conferma Password: <sup>*</sup></label>
                    <input type="password" name="confirm_password" class="form-control form-control-lg <?= (!empty($data['confirm_password_err'])) ? 'is-invalid' : ''; ?>" value="<?= $data['confirm_password']; ?>">
                    <span class="invalid-feedback"><?= $data['confirm_password_err']; ?></span>
                </div>

                <div class="row mt-4">
                    <div class="col">
                        <input type="submit" value="Reimposta Password" class="btn btn-success btn-block">
                    </div>
                    <div class="col">
                        <a href="<?= URLROOT ?>/users" class="btn btn-light btn-block">Annulla</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require APPROOT . '/Views/includes/footer.php'; ?>