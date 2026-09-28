<main class="auth-page">
    <div class="auth-card wide">

        <div class="text-center mb-4">
            <h1 class="h2 fw-bold">Rejoignez PENDAFITNESS</h1>
            <p class="text-secondary">
                Votre compte membre en moins d'une minute.
            </p>
        </div>

        <form method="post" novalidate>
            <input type="hidden" name="csrf" value="<?= csrf() ?>">

            <div class="row g-3">

                <!-- PRÉNOM -->
                <div class="col-md-6">
                    <label class="form-label" for="firstname">Prénom</label>

                    <input
                        id="firstname"
                        name="firstname"
                        type="text"
                        maxlength="80"
                        autocomplete="given-name"
                        class="form-control <?= !empty($errors['firstname']) ? 'is-invalid' : '' ?>"
                        value="<?= e($_POST['firstname'] ?? '') ?>"
                        required
                    >

                    <?php if (!empty($errors['firstname'])): ?>
                        <div class="invalid-feedback">
                            <?= e($errors['firstname']) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- NOM -->
                <div class="col-md-6">
                    <label class="form-label" for="lastname">Nom</label>

                    <input
                        id="lastname"
                        name="lastname"
                        type="text"
                        maxlength="80"
                        autocomplete="family-name"
                        class="form-control <?= !empty($errors['lastname']) ? 'is-invalid' : '' ?>"
                        value="<?= e($_POST['lastname'] ?? '') ?>"
                        required
                    >

                    <?php if (!empty($errors['lastname'])): ?>
                        <div class="invalid-feedback">
                            <?= e($errors['lastname']) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- E-MAIL -->
                <div class="col-md-6">
                    <label class="form-label" for="email">E-mail</label>

                    <input
                        id="email"
                        name="email"
                        type="email"
                        maxlength="190"
                        autocomplete="email"
                        class="form-control <?= !empty($errors['email']) ? 'is-invalid' : '' ?>"
                        value="<?= e($_POST['email'] ?? '') ?>"
                        required
                    >

                    <?php if (!empty($errors['email'])): ?>
                        <div class="invalid-feedback">
                            <?= e($errors['email']) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- TÉLÉPHONE -->
                <div class="col-md-6">
                    <label class="form-label" for="phone">Téléphone</label>

                    <input
                        id="phone"
                        name="phone"
                        type="tel"
                        maxlength="30"
                        autocomplete="tel"
                        class="form-control <?= !empty($errors['phone']) ? 'is-invalid' : '' ?>"
                        value="<?= e($_POST['phone'] ?? '') ?>"
                    >

                    <?php if (!empty($errors['phone'])): ?>
                        <div class="invalid-feedback">
                            <?= e($errors['phone']) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- VILLE -->
                <div class="col-md-6">
                    <label class="form-label" for="city">Ville</label>

                    <input
                        id="city"
                        name="city"
                        type="text"
                        maxlength="120"
                        autocomplete="address-level2"
                        class="form-control <?= !empty($errors['city']) ? 'is-invalid' : '' ?>"
                        value="<?= e($_POST['city'] ?? '') ?>"
                    >

                    <?php if (!empty($errors['city'])): ?>
                        <div class="invalid-feedback">
                            <?= e($errors['city']) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- MOT DE PASSE -->
                <div class="col-md-6">
                    <label class="form-label" for="regpassword">
                        Mot de passe
                    </label>

                    <div class="input-group">
                        <input
                            id="regpassword"
                            type="password"
                            name="password"
                            autocomplete="new-password"
                            class="form-control <?= !empty($errors['password']) ? 'is-invalid' : '' ?>"
                            minlength="8"
                            required
                        >

                        <button
                            class="btn btn-outline-secondary password-toggle"
                            type="button"
                            data-target="regpassword"
                            aria-label="Afficher ou masquer le mot de passe"
                        >
                            <i class="bi bi-eye"></i>
                        </button>

                        <?php if (!empty($errors['password'])): ?>
                            <div class="invalid-feedback">
                                <?= e($errors['password']) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <small class="text-secondary">
                        8 caractères minimum.
                    </small>
                </div>

            </div>

            <button class="btn btn-accent btn-lg w-100 mt-4" type="submit">
                Créer mon compte
            </button>

        </form>

    </div>
</main>