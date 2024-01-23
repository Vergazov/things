<div class="page-wrapper">
    <div class="container container--with-sidebar">
        <header class="main-header">
            <a href="#">
                <img src="../img/logo.png" width="153" height="42" alt="Логитип Дела в порядке">
            </a>

            <div class="main-header__side">
                <a class="main-header__side-item button button--transparent" href="form-authorization.html">Войти</a>
            </div>
        </header>

        <div class="content">
            <section class="content__side">
                <p class="content__side-info">Если у вас уже есть аккаунт, авторизуйтесь на сайте</p>

                <a class="button button--transparent content__side-button" href="form-authorization.html">Войти</a>
            </section>

            <main class="content__main">
                <h2 class="content__main-heading">Регистрация аккаунта</h2>

                <form class="form" action="register.php" method="post" autocomplete="off">
                    <div class="form__row">
                        <label class="form__label" for="email">E-mail <sup>*</sup></label>

                        <input class="form__input form__input--error" type="text" name="email" id="email" value="" placeholder="Введите e-mail">

                        <p class="form__message">E-mail введён некорректно</p>
                    </div>

                    <div class="form__row">
                        <label class="form__label" for="password">Пароль <sup>*</sup></label>

                        <input class="form__input" type="password" name="password" id="password" value="" placeholder="Введите пароль">
                    </div>

                    <div class="form__row">
                        <label class="form__label" for="name">Имя <sup>*</sup></label>

                        <input class="form__input" type="text" name="name" id="name" value="" placeholder="Введите имя">
                    </div>

                    <div class="form__row form__row--controls">
                        <p class="error-message">Пожалуйста, исправьте ошибки в форме</p>

                        <input class="button" type="submit" name="" value="Зарегистрироваться">
                    </div>
                </form>
            </main>
        </div>
    </div>
</div>