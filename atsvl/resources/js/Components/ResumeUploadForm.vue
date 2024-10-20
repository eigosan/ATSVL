<template>

    <form @submit.prevent="uploadResume">

        <input type="file" @change="handleFileChange">
        <button type="submit">Upload Resume</button>

    </form>

</template>
