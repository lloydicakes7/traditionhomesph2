<section class="py-5 bg-secondary text-white" id="portal">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-12 col-lg-8 mb-3 mb-lg-0">
                <h2 class="h3 mb-1">Already a homeowner?</h2>
                <p class="mb-0" style="opacity: .85;">
                    Sign in to view your statement of account, file a request or concern, and keep your
                    household details up to date. Accounts are issued by the HOA office.
                </p>
            </div>

            <div class="col-12 col-lg-4 text-lg-right">
                <a href="{{ auth('homeowner')->check() ? route('portal.dashboard') : route('login') }}"
                    class="btn btn-primary btn-lg">
                    <i class="fas fa-fw fa-sign-in-alt mr-1"></i>Login
                </a>
            </div>
        </div>
    </div>
</section>
