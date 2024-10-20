<template>

    <form @submit.prevent="uploadResume">

        <input type="file" @change="handleFileChange">
        <button type="submit">Upload Resume</button>

    </form>

</template>

<script>
export default {
    data() {
        return {

            resumeFile: null,

        };




    },
    methods: {
        handleFileChange(event) {
            this.resumeFile = event.target.files[0];

        },
        uploadResume() {
            // Call API to upload resume
            axios.post('api/resumes', {
                resume: this.resumeFile,
            })
            .then((response) => {
                console.log(response.data);
            })
            .catch((error) => {
                console.error(error);
            });
        },

    },


};



</script>
