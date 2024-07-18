<template>
    <div>
        <h1>Users Data</h1>
        <div v-if="users.length === 0">No data available.</div>
        <table v-else>
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="user in users" :key="user.id">
                    <td>{{ user.id }}</td>
                    <td>{{ user.name }}</td>
                    <td>{{ user.email }}</td>
                    <td>
                        <button @click="editUser(user)">Edit</button>
                        <button @click="deleteUser(user.id)">Delete</button>
                    </td>
                </tr>
            </tbody>
        </table>
        <div>
            <h2>{{ editMode ? 'Edit User' : 'Add New User' }}</h2>
            <form @submit.prevent="saveUser">
                <input type="text" v-model="formData.name" placeholder="Name" required />
                <input type="email" v-model="formData.email" placeholder="Email" required />
                <button type="submit">{{ editMode ? 'Update' : 'Add' }} User</button>
            </form>
        </div>
    </div>
</template>

<script>
import { defineComponent, ref, onMounted } from 'vue';
import axios from 'axios';

export default defineComponent({
    name: 'UsersView',
    setup() {
        const users = ref([]);
        const formData = ref({ id: null, name: '', email: '' });
        const editMode = ref(false);

        async function fetchUsers() {
            const url = process.env.VUE_APP_BACKEND_URL + '/backend/users.php';
            try {
                const response = await axios.get(url);
                if (response.data.status === 'success') {
                    users.value = response.data.users;
                } else {
                    console.error('Error fetching data:', response.data.message);
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        async function saveUser() {
            const url = process.env.VUE_APP_BACKEND_URL + '/backend/users.php';
            try {
                const response = await axios.post(url, formData.value);
                if (response.data.status === 'success') {
                    fetchUsers();
                    formData.value = { id: null, name: '', email: '' };
                    editMode.value = false;
                } else {
                    console.error('Error saving user:', response.data.message);
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        function editUser(user) {
            formData.value = { ...user };
            editMode.value = true;
        }

        async function deleteUser(id) {
            const url = process.env.VUE_APP_BACKEND_URL + '/backend/users.php?action=delete';
            try {
                const response = await axios.post(url, { id });
                if (response.data.status === 'success') {
                    fetchUsers();
                } else {
                    console.error('Error deleting user:', response.data.message);
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        onMounted(() => {
            fetchUsers();
        });

        return {
            users,
            formData,
            editMode,
            saveUser,
            editUser,
            deleteUser
        }
    }
});
</script>

<style lang="scss" scoped>
#app {
    font-family: Avenir, Helvetica, Arial, sans-serif;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    text-align: center;
    color: #2c3e50;
    margin-top: 60px;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

th,
td {
    border: 1px solid #ddd;
    padding: 8px;
}

th {
    background-color: #f2f2f2;
}

button {
    padding: 8px 16px;
    margin: 4px;
    border: none;
    background-color: #42b983;
    color: white;
    cursor: pointer;
    transition: background-color 0.3s;

    &:hover {
        background-color: #2c3e50;
    }
}
</style>
