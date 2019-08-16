<template>
  <div>
    <h2>Products</h2>
    <button @click="d3Thing()" class="btn">D3</button>
    <p>Total Products: {{ total_products }}</p>
    <div id="graph" class="col-md-3 col-sm-3 col-xs-3">&nbsp;</div>
    <nav aria-label="Page navigation example">
      <ul class="pagination">
        <li v-bind:class="[{disabled: !pagination.prev_page_url}]" class="page-item">
          <a class="page-link" href="#" @click="fetchProducts(pagination.prev_page_url)">Previous</a>
        </li>
        <li class="page-item disabled">
          <a
            class="page-link text-dark"
            href="#"
          >Page {{pagination.current_page}} of {{pagination.last_page}}</a>
        </li>
        <li v-bind:class="[{disabled: !pagination.next_page_url}]" class="page-item">
          <a class="page-link" href="#" @click="fetchProducts(pagination.next_page_url)">Next</a>
        </li>
      </ul>
    </nav>
    <form @submit.prevent="searchProduct" class="form-inline mb-3">
      <input
        type="text"
        class="form-control mr-2"
        placeholder="Search barcode..."
        v-model="search_barcode"
      />
      <button type="submit" class="btn btn-primary">Search</button>
    </form>
    <table class="table table-striped table-light">
      <thead class="table-info">
        <tr>
          <th scope="col">Barcode</th>
          <th scope="col">Name</th>
          <th scope="col">Price</th>
          <th scope="col">Sales</th>
          <th scope="col">Controls</th>
          <th scope="col"></th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="product in products" v-bind:key="product.id">
          <td>{{ product.id }}</td>
          <td>{{ product.name }}</td>
          <td>Price: £{{ product.price }}</td>
          <td>Sales: {{ product.sales }}</td>
          <td>
            <button @click="editProduct(product)" class="btn btn-warning">Edit</button>
          </td>
          <td>
            <button @click="deleteProduct(product.id)" class="btn btn-danger">Delete</button>
          </td>
        </tr>
      </tbody>
    </table>
    <form @submit.prevent="addProduct" class="mb-3">
      <h3>Editor</h3>
      <div class="form-group">
        <input type="text" class="form-control" placeholder="barcode" v-model="product.id" />
      </div>
      <div class="form-group">
        <input type="text" class="form-control" placeholder="name" v-model="product.name" />
      </div>
      <div class="form-group">
        <textarea class="form-control" placeholder="description" v-model="product.description"></textarea>
      </div>
      <div class="form-group">
        <input type="text" class="form-control" placeholder="price" v-model="product.price" />
      </div>
      <div class="form-group">
        <input type="text" class="form-control" placeholder="cost" v-model="product.cost" />
      </div>
      <div class="form-group">
        <input type="text" class="form-control" placeholder="sales" v-model="product.sales" />
      </div>
      <button type="submit" class="btn btn-primary btn-block">Save</button>
    </form>
  </div>
</template>

<script>
export default {
  data() {
    return {
      products: [],
      product: {
        id: "",
        name: "",
        description: "",
        price: "",
        cost: "",
        sales: ""
      },
      search_barcode: "",
      total_products: "",
      product_id: "",
      pagination: {},
      edit: false
    };
  },

  created() {
    this.fetchProducts();
  },

  methods: {
    fetchProducts(page_url) {
      let vm = this;
      page_url = page_url || "api/products";
      fetch(page_url)
        .then(res => res.json())
        .then(res => {
          this.products = res.data;
          this.total_products = res.meta.total;
          vm.makePagination(res.meta, res.links);
          this.drawGraph();
        })
        .catch(err => console.log(err));
    },
    makePagination(meta, links) {
      let pagination = {
        current_page: meta.current_page,
        last_page: meta.last_page,
        next_page_url: links.next,
        prev_page_url: links.prev
      };

      this.pagination = pagination;
    },
    deleteProduct(id) {
      if (confirm("Are you sure you want to delete this product?")) {
        fetch(`api/product/${id}`, {
          method: "delete"
        })
          .then(res => res.json())
          .then(data => {
            this.fetchProducts();
          })
          .catch(err => console.log(err));
      }
    },
    searchProduct() {
      fetch(`api/product/${this.search_barcode}`)
        .then(res => res.json())
        .then(res => {
          this.product.id = res.data.id;
          this.product.name = res.data.name;
          this.product.description = res.data.description;
          this.product.price = res.data.price;
          this.product.cost = res.data.cost;
          this.product.sales = res.data.sales;
          this.edit = true;
        })
        .catch(err => console.log(err));
    },
    addProduct() {
      if (this.product.id !== "") {
        if (this.edit == false) {
          fetch("api/product", {
            method: "post",
            body: JSON.stringify(this.product),
            headers: {
              "content-type": "application/json"
            }
          })
            .then(res => res.json())
            .then(data => {
              this.product.id = "";
              this.product.name = "";
              this.product.description = "";
              this.product.price = "";
              this.product.cost = "";
              this.product.sales = "";
              this.fetchProducts();
            })
            .catch(err => console.log(err));
        } else {
          fetch("api/product", {
            method: "put",
            body: JSON.stringify(this.product),
            headers: {
              "content-type": "application/json"
            }
          })
            .then(res => res.json())
            .then(data => {
              this.product.id = "";
              this.product.name = "";
              this.product.description = "";
              this.product.price = "";
              this.product.cost = "";
              this.product.sales = "";
              this.fetchProducts();
            })
            .catch(err => console.log(err));
        }
      } else {
        alert("Must enter a barcode");
      }
    },
    scrollToTop() {
      window.scrollTo(0, 0);
    },
    editProduct(product) {
      this.edit = true;
      this.product.id = product.id;
      this.product.product_id = product.id;
      this.product.name = product.name;
      this.product.description = product.description;
      this.product.price = product.price;
      this.product.cost = product.cost;
      this.product.sales = product.sales;
    },
    drawGraph() {
      var dataArray = new Array(this.products.length);
      var width = 500;
      var height = 500;
      for (var i = 0; i < dataArray.length; i++) {
        dataArray[i] = this.products[i].sales;
      }

      d3.select("#graph")
        .selectAll("*")
        .remove();

      var widthScale = d3
        .scaleLinear()
        .domain([0, 60])
        .range([0, width]);

      var color = d3
        .scaleLinear()
        .domain([0, 60])
        .range(["red", "blue"]);

      var axis = d3.axisBottom().ticks(5).scale(widthScale);

      var canvas = d3
        .select("#graph")
        .append("svg")
        .attr("width", width)
        .attr("height", height)
        .append("g")
        .attr("transform", "translate(20, 0)")
        .call(axis);

      var bars = canvas
        .selectAll("rect")
        .data(dataArray)
        .enter()
        .append("rect")
        .attr("width", function(d) {
          return d;
        })
        .attr("height", 50)
        .attr("fill", "blue")
        .attr("y", function(d, i) {
          return i * 100;
        });

      canvas
        .append("g")
        .attr("transform", "translate(0, 400)")
        .call(axis);
    }
  }
};
</script>