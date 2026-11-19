    <?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    class CreateUsersTable extends Migration
    {
        /**
         * Run the migrations.
         *
         * @return void
         */
        public function up()
        {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->foreignId('resident_id')->nullable()->constrained('residents')->onDelete('set null');
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('registration_status', 20)->default('approved');
                $table->boolean('is_active')->default(true);
                $table->string('profile_status', 20)->default('verified');
                $table->timestamp('profile_submitted_at')->nullable();
                $table->timestamp('profile_verified_at')->nullable();
                $table->foreignId('profile_verified_by_admin_id')->nullable()->constrained('admins')->nullOnDelete();
                $table->string('profile_rejection_note')->nullable();
                $table->string('password');
                $table->boolean('must_change_password')->default(false);
                $table->timestamp('terms_accepted_at')->nullable();
                $table->timestamp('privacy_accepted_at')->nullable();
                $table->timestamp('last_login_at')->nullable();
                $table->rememberToken();
                $table->timestamps();

                $table->index('email', 'idx_users_email');
                $table->index('resident_id', 'idx_users_resident_id');
                $table->index('registration_status', 'idx_users_registration_status');
                $table->index('profile_status', 'idx_users_profile_status');
            });
        }

        /**
         * Reverse the migrations.
         *
         * @return void
         */
        public function down()
        {
            Schema::dropIfExists('users');
        }
    }
