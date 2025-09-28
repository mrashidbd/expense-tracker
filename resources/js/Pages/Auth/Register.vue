<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import {Head, Link, useForm} from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};

</script>

<template>
    <GuestLayout>
        <Head title="Register"/>
        <div class="flex flex-col">
            <h2 class="text-center mb-8 text-2xl">Sorry! <br>This is a private app, we are not accepting any user
                registration</h2>

            <div class="flex justify-center align-middle">
                <a :href="route('login')" class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-gray-900">Back to
                    Login</a>
            </div>
        </div>

        <!--        <form @submit.prevent="submit">-->
        <!--            <div>-->
        <!--                <InputLabel for="name" value="Name" />-->

        <!--                <TextInput-->
        <!--                    id="name"-->
        <!--                    type="text"-->
        <!--                    class="mt-1 block w-full"-->
        <!--                    v-model="form.name"-->
        <!--                    required-->
        <!--                    autofocus-->
        <!--                    autocomplete="name"-->
        <!--                />-->

        <!--                <InputError class="mt-2" :message="form.errors.name" />-->
        <!--            </div>-->

        <!--            <div class="mt-4">-->
        <!--                <InputLabel for="email" value="Email" />-->

        <!--                <TextInput-->
        <!--                    id="email"-->
        <!--                    type="email"-->
        <!--                    class="mt-1 block w-full"-->
        <!--                    v-model="form.email"-->
        <!--                    required-->
        <!--                    autocomplete="username"-->
        <!--                />-->

        <!--                <InputError class="mt-2" :message="form.errors.email" />-->
        <!--            </div>-->

        <!--            <div class="mt-4">-->
        <!--                <InputLabel for="password" value="Password" />-->

        <!--                <TextInput-->
        <!--                    id="password"-->
        <!--                    type="password"-->
        <!--                    class="mt-1 block w-full"-->
        <!--                    v-model="form.password"-->
        <!--                    required-->
        <!--                    autocomplete="new-password"-->
        <!--                />-->

        <!--                <InputError class="mt-2" :message="form.errors.password" />-->
        <!--            </div>-->

        <!--            <div class="mt-4">-->
        <!--                <InputLabel-->
        <!--                    for="password_confirmation"-->
        <!--                    value="Confirm Password"-->
        <!--                />-->

        <!--                <TextInput-->
        <!--                    id="password_confirmation"-->
        <!--                    type="password"-->
        <!--                    class="mt-1 block w-full"-->
        <!--                    v-model="form.password_confirmation"-->
        <!--                    required-->
        <!--                    autocomplete="new-password"-->
        <!--                />-->

        <!--                <InputError-->
        <!--                    class="mt-2"-->
        <!--                    :message="form.errors.password_confirmation"-->
        <!--                />-->
        <!--            </div>-->

        <!--            <div class="mt-4 flex items-center justify-end">-->
        <!--                <Link-->
        <!--                    :href="route('login')"-->
        <!--                    class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"-->
        <!--                >-->
        <!--                    Already registered?-->
        <!--                </Link>-->

        <!--                <PrimaryButton-->
        <!--                    class="ms-4"-->
        <!--                    :class="{ 'opacity-25': form.processing }"-->
        <!--                    :disabled="form.processing"-->
        <!--                >-->
        <!--                    Register-->
        <!--                </PrimaryButton>-->
        <!--            </div>-->
        <!--        </form>-->
    </GuestLayout>
</template>
