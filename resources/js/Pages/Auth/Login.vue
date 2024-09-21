<script setup>
import Checkbox from "@/Components/Checkbox.vue";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: "",
    password: "",
    remember: false,
});

const submit = () => {
    form.post(route("login"), {
        onFinish: () => form.reset("password"),
    });
};
</script>

<template>
    <GuestLayout>

        <Head title="Log in" />

        <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Email" />

                <TextInput id="email" type="email" class="mt-1 block w-full" v-model="form.email" required autofocus
                    autocomplete="username" />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Password" />

                <TextInput id="password" type="password" class="mt-1 block w-full" v-model="form.password" required
                    autocomplete="current-password" />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="flex items-center justify-between mt-4">
                <div class="block">
                    <label class="flex items-center">
                        <Checkbox name="remember" v-model:checked="form.remember" />
                        <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">
                            Remember me
                        </span>
                    </label>
                </div>

                <div class="flex gap-3 items-center justify-between">
                    <Link v-if="canResetPassword" aria-label="Forgot your password?" :href="route('password.request')"
                        class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                    Forgot your password?
                    </Link>
                </div>
            </div>
            <PrimaryButton aria-label="Sign in" class="w-full mt-4 justify-center"
                :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                Sign in
            </PrimaryButton>
            <template v-if="canResetPassword">
                <div class="mt-2">
                    <Link :href="route('register')" aria-label="Register for Zosmed"
                        class="underline text-sm text-center text-gray-800 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                    don't have an account?
                    </Link>
                </div>
                <div class="mt-4 text-sm text-center text-gray-800 dark:text-gray-400">
                    Or sign in with
                </div>

                <div class="mt-4">
                    <a :href="route('auth.google')" aria-label="Sign in with Google"
                        class="w-full flex justify-center items-center gap-2 py-2 px-4 border border-gray-300 rounded-md dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 text-sm">
                        <img src="Google__G__logo.svg" />
                        Sign in with Google
                    </a>
                </div>
            </template>
        </form>
    </GuestLayout>
</template>
