
<?php include 'partials/header.php';
$Email = $_SESSION['signin-data']['Email'] ?? null;
$pass = $_SESSION['signin-data']['Password'] ?? null;
?> 




        <section>
        <div class="section__container">
            <h2>Bank</h2>
            <div class="form__container">
                <div class="information">
                    Hoşgeldiniz
                    <?php if (isset($_SESSION['Signin-fail'])) : ?>
                        <p><?= $_SESSION['Signin-fail'] ?></p>
                    <?php unset($_SESSION['Signin-fail']);
                    endif ?>
                </div>

                <form class="sign_form" action="signin-logic.php" method="POST">
                    <p>Email </p><input value="<?=$Email?>" name="Email" placeholder="Email" type="email">
                    <p>Password </p><input value="<?=$pass?>" name="Password" placeholder="Password" type="password">
                    <div class="form_submit_container">
                        <button class="form__button" name="submit" type="submit">Login</button>
                        <a href="signup.php">Don't have an account</a>
                    </div>
                </form>
            </div>
        </div>
    </section>


   <?php include 'partials/footer.php'?>