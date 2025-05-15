<?php $title = 'Registrazione - ' . $tenant_name; ?>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card card-body bg-light mt-5">
            <h2 class="text-center mb-4">Registrazione Nuovo Utente</h2>
            <p class="text-center">Sei stato invitato a registrarti su <strong><?= $tenant_name ?></strong></p>
            
            <?php if (isset($errors['general'])): ?>
                <div class="alert alert-danger"><?= $errors['general'] ?></div>
            <?php endif; ?>
            
            <form action="<?= URLROOT ?>/invites/process/<?= $token ?>" method="post" class="needs-validation" novalidate>
                <input type="hidden" name="email" value="<?= $email ?>">
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="name">Nome <sup>*</sup></label>
                            <input type="text" name="name" id="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" 
                                   value="<?= isset($name) ? $name : '' ?>" required>
                            <?php if (isset($errors['name'])): ?>
                                <div class="invalid-feedback"><?= $errors['name'] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="lastname">Cognome <sup>*</sup></label>
                            <input type="text" name="lastname" id="lastname" class="form-control <?= isset($errors['lastname']) ? 'is-invalid' : '' ?>" 
                                   value="<?= isset($lastname) ? $lastname : '' ?>" required>
                            <?php if (isset($errors['lastname'])): ?>
                                <div class="invalid-feedback"><?= $errors['lastname'] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="email">Email <sup>*</sup></label>
                            <input type="email" name="email" id="email" class="form-control" value="<?= $email ?>" readonly>
                            <small class="form-text text-muted">L'email è già precompilata dall'invito ricevuto.</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="phone">Telefono <sup>*</sup></label>
                            <input type="tel" name="phone" id="phone" class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>" 
                                   value="<?= isset($phone) ? $phone : '' ?>" required>
                            <?php if (isset($errors['phone'])): ?>
                                <div class="invalid-feedback"><?= $errors['phone'] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="password">Password <sup>*</sup></label>
                            <input type="password" name="password" id="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" required>
                            <small class="form-text text-muted">Minimo 6 caratteri</small>
                            <?php if (isset($errors['password'])): ?>
                                <div class="invalid-feedback"><?= $errors['password'] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="confirm_password">Conferma Password <sup>*</sup></label>
                            <input type="password" name="confirm_password" id="confirm_password" class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>" required>
                            <?php if (isset($errors['confirm_password'])): ?>
                                <div class="invalid-feedback"><?= $errors['confirm_password'] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="birthdate">Data di Nascita <sup>*</sup></label>
                            <input type="date" name="birthdate" id="birthdate" class="form-control <?= isset($errors['birthdate']) ? 'is-invalid' : '' ?>" 
                                   value="<?= isset($birthdate) ? $birthdate : '' ?>" required>
                            <?php if (isset($errors['birthdate'])): ?>
                                <div class="invalid-feedback"><?= $errors['birthdate'] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="tax_code">Codice Fiscale <sup>*</sup></label>
                            <input type="text" name="tax_code" id="tax_code" class="form-control <?= isset($errors['tax_code']) ? 'is-invalid' : '' ?>" 
                                   value="<?= isset($tax_code) ? $tax_code : '' ?>" required>
                            <?php if (isset($errors['tax_code'])): ?>
                                <div class="invalid-feedback"><?= $errors['tax_code'] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="form-group mb-3">
                            <label for="address">Indirizzo <sup>*</sup></label>
                            <input type="text" name="address" id="address" class="form-control <?= isset($errors['address']) ? 'is-invalid' : '' ?>" 
                                   value="<?= isset($address) ? $address : '' ?>" required>
                            <?php if (isset($errors['address'])): ?>
                                <div class="invalid-feedback"><?= $errors['address'] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="city">Città <sup>*</sup></label>
                            <input type="text" name="city" id="city" class="form-control <?= isset($errors['city']) ? 'is-invalid' : '' ?>" 
                                   value="<?= isset($city) ? $city : '' ?>" required>
                            <?php if (isset($errors['city'])): ?>
                                <div class="invalid-feedback"><?= $errors['city'] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mb-3">
                            <label for="province">Provincia <sup>*</sup></label>
                            <input type="text" name="province" id="province" class="form-control <?= isset($errors['province']) ? 'is-invalid' : '' ?>" 
                                   value="<?= isset($province) ? $province : '' ?>" required>
                            <?php if (isset($errors['province'])): ?>
                                <div class="invalid-feedback"><?= $errors['province'] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mb-3">
                            <label for="zip">CAP <sup>*</sup></label>
                            <input type="text" name="zip" id="zip" class="form-control <?= isset($errors['zip']) ? 'is-invalid' : '' ?>" 
                                   value="<?= isset($zip) ? $zip : '' ?>" required>
                            <?php if (isset($errors['zip'])): ?>
                                <div class="invalid-feedback"><?= $errors['zip'] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="weight">Peso (kg) <sup>*</sup></label>
                            <input type="number" step="0.1" name="weight" id="weight" class="form-control <?= isset($errors['weight']) ? 'is-invalid' : '' ?>" 
                                   value="<?= isset($weight) ? $weight : '' ?>" required>
                            <?php if (isset($errors['weight'])): ?>
                                <div class="invalid-feedback"><?= $errors['weight'] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="height">Altezza (cm) <sup>*</sup></label>
                            <input type="number" step="0.1" name="height" id="height" class="form-control <?= isset($errors['height']) ? 'is-invalid' : '' ?>" 
                                   value="<?= isset($height) ? $height : '' ?>" required>
                            <?php if (isset($errors['height'])): ?>
                                <div class="invalid-feedback"><?= $errors['height'] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group mb-3">
                            <p><sup>*</sup> Campi obbligatori</p>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col">
                        <input type="submit" value="Registrati" class="btn btn-success btn-block">
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>