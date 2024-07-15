<template>
  <div id="app">
    <img alt="Vue logo" src="./assets/logo.png">
    <h1>Film Data</h1>
    <div v-if="data.length === 0">No data available.</div>
    <table v-else>
      <thead>
        <tr>
          <th>Film ID</th>
          <th>Title</th>
          <th>Description</th>
          <th>Release Year</th>
          <th>Language ID</th>
          <th>Original Language ID</th>
          <th>Rental Duration</th>
          <th>Rental Rate</th>
          <th>Length</th>
          <th>Replacement Cost</th>
          <th>Rating</th>
          <th>Special Features</th>
          <th>Last Update</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="item in data" :key="item.film_id">
          <td>{{ item.film_id }}</td>
          <td>{{ item.title }}</td>
          <td>{{ item.description }}</td>
          <td>{{ item.release_year }}</td>
          <td>{{ item.language_id }}</td>
          <td>{{ item.original_language_id }}</td>
          <td>{{ item.rental_duration }}</td>
          <td>{{ item.rental_rate }}</td>
          <td>{{ item.length }}</td>
          <td>{{ item.replacement_cost }}</td>
          <td>{{ item.rating }}</td>
          <td>{{ item.special_features }}</td>
          <td>{{ item.last_update }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
import { defineComponent, ref, onMounted } from 'vue'
import axios from 'axios';

export default defineComponent({
  name: 'App',
  setup() {

    console.log(process.env.VUE_APP_BACKEND_URL)

    const data = ref([]);
    const message = ref('test');

    async function fetchData() {
      var url = process.env.VUE_APP_BACKEND_URL + '/backend/api.php'
      try {
        const response = await axios.post(url, {
          message: message.value
        });

        if (response.data.status === 'success') {
          data.value = response.data.message;
        } else {
          console.error('Error fetching data:', response.data.message);
        }
      } catch (error) {
        console.error('Error:', error);
      }
    }

    onMounted(() => {
      fetchData();
    });

    return {
      data
    }
  }
})
</script>

<style lang="scss">
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
</style>
