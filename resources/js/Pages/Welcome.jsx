import React, { useState, useMemo } from "react";
import {
  Layout,
  Input,
  Card,
  Button,
  Typography,
  Space,
  Row,
  Col,
  Tag,
  Segmented,
  Modal,
  Select,
  Form,
  InputNumber,
  Result,
  notification,
} from "antd";
import {
  SearchOutlined,
  ShoppingCartOutlined,
  DeleteOutlined,
  PlusOutlined,
  MinusOutlined,
} from "@ant-design/icons";

const { Header, Content, Sider } = Layout;
const { Title, Text } = Typography;
const { Option } = Select;

// data produk
const initialProducts = [
  { id: 1, name: "Beras Premium 5kg", category: "makanan", price: 75000, wholesale: 70000, stock: 25, image: "🌾" },
  { id: 2, name: "Minyak Goreng 2L", category: "makanan", price: 32000, wholesale: 30000, stock: 18, image: "🛢️" },
  { id: 3, name: "Gula Pasir 1kg", category: "makanan", price: 15000, wholesale: 14000, stock: 32, image: "🍯" },
  { id: 17, name: "Air Mineral 600ml", category: "minuman", price: 3000, wholesale: 2500, stock: 48, image: "💧" },
  { id: 5, name: "Sabun Cuci Piring", category: "kebersihan", price: 8000, wholesale: 7500, stock: 28, image: "🧽" },
];

const categories = [
  { label: "🏪 Semua", value: "all" },
  { label: "🍽️ Makanan", value: "makanan" },
  { label: "🥤 Minuman", value: "minuman" },
  { label: "🧽 Kebersihan", value: "kebersihan" },
];

export default function POSApp() {
  const [products, setProducts] = useState(initialProducts);
  const [cart, setCart] = useState([]);
  const [category, setCategory] = useState("all");
  const [search, setSearch] = useState("");
  const [checkoutVisible, setCheckoutVisible] = useState(false);
  const [successVisible, setSuccessVisible] = useState(false);
  const [form] = Form.useForm();
  const [api, contextHolder] = notification.useNotification();

  // filter produk
  const filteredProducts = useMemo(() => {
    return products.filter((p) => {
      const matchCategory = category === "all" || p.category === category;
      const matchSearch = p.name.toLowerCase().includes(search.toLowerCase());
      return matchCategory && matchSearch;
    });
  }, [products, category, search]);

  // total belanja
  const total = useMemo(() => {
    return cart.reduce(
      (sum, item) =>
        sum +
        item.quantity *
          (item.priceType === "wholesale" ? item.wholesale : item.price),
      0
    );
  }, [cart]);

  const addToCart = (product) => {
    const exist = cart.find((c) => c.id === product.id);
    if (exist) {
      if (exist.quantity < product.stock) {
        setCart(
          cart.map((c) =>
            c.id === product.id ? { ...c, quantity: c.quantity + 1 } : c
          )
        );
      } else {
        api.warning({ message: "Stok habis", description: product.name });
      }
    } else {
      setCart([
        ...cart,
        { ...product, quantity: 1, priceType: "regular" },
      ]);
    }
  };

  const updateQuantity = (id, qty) => {
    if (qty <= 0) {
      setCart(cart.filter((c) => c.id !== id));
    } else {
      setCart(cart.map((c) => (c.id === id ? { ...c, quantity: qty } : c)));
    }
  };

  const changePriceType = (id, type) => {
    setCart(cart.map((c) => (c.id === id ? { ...c, priceType: type } : c)));
  };

  const handleCheckout = (values) => {
    const received = values.received || 0;
    if (received < total) {
      api.error({ message: "Uang kurang", description: "Pembayaran gagal" });
      return;
    }
    // kurangi stok
    const newProducts = products.map((p) => {
      const item = cart.find((c) => c.id === p.id);
      if (item) return { ...p, stock: p.stock - item.quantity };
      return p;
    });
    setProducts(newProducts);
    setCart([]);
    setCheckoutVisible(false);
    setSuccessVisible(true);
    form.resetFields();
  };

  return (
    <Layout style={{ minHeight: "100vh" }}>
      {contextHolder}
      <Header style={{ background: "linear-gradient(135deg,#0891b2,#059669)" }}>
        <Title level={3} style={{ color: "white", margin: 0 }}>
          🏪 Toko Berkah Jaya
        </Title>
      </Header>
      <Layout>
        <Content style={{ padding: 20 }}>
          <Input
            prefix={<SearchOutlined />}
            placeholder="Cari produk..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            style={{ marginBottom: 20 }}
          />
          <Segmented
            block
            options={categories}
            value={category}
            onChange={setCategory}
            style={{ marginBottom: 20 }}
          />
          <Row gutter={[16, 16]}>
            {filteredProducts.map((p) => (
              <Col xs={12} sm={8} md={6} key={p.id}>
                <Card
                  hoverable
                  actions={[
                    <Button
                      type="primary"
                      onClick={() => addToCart(p)}
                      disabled={p.stock === 0}
                    >
                      {p.stock === 0 ? "Habis" : "Tambah"}
                    </Button>,
                  ]}
                >
                  <div style={{ fontSize: 32, textAlign: "center" }}>
                    {p.image}
                  </div>
                  <Title level={5}>{p.name}</Title>
                  <Tag color={p.stock === 0 ? "red" : "green"}>
                    Stok: {p.stock}
                  </Tag>
                  <div>
                    <Text>Reguler: Rp {p.price.toLocaleString()}</Text>
                  </div>
                  <div>
                    <Text>Grosir: Rp {p.wholesale.toLocaleString()}</Text>
                  </div>
                </Card>
              </Col>
            ))}
          </Row>
        </Content>

        <Sider width={350} theme="light" style={{ padding: 20 }}>
          <Title level={4}>
            <ShoppingCartOutlined /> Keranjang
          </Title>
          <div style={{ marginBottom: 16 }}>
            {cart.length === 0 ? (
              <Text type="secondary">Kosong</Text>
            ) : (
              cart.map((item) => (
                <Card
                  size="small"
                  key={item.id}
                  style={{ marginBottom: 10 }}
                  actions={[
                    <Button
                      icon={<DeleteOutlined />}
                      type="text"
                      danger
                      onClick={() =>
                        setCart(cart.filter((c) => c.id !== item.id))
                      }
                    />,
                  ]}
                >
                  <div className="flex justify-between">
                    <Text>{item.name}</Text>
                  </div>
                  <Space>
                    <Button
                      icon={<MinusOutlined />}
                      onClick={() =>
                        updateQuantity(item.id, item.quantity - 1)
                      }
                    />
                    <Text>{item.quantity}</Text>
                    <Button
                      icon={<PlusOutlined />}
                      onClick={() =>
                        updateQuantity(item.id, item.quantity + 1)
                      }
                    />
                  </Space>
                  <Select
                    size="small"
                    value={item.priceType}
                    onChange={(val) => changePriceType(item.id, val)}
                  >
                    <Option value="regular">Reguler</Option>
                    <Option value="wholesale">Grosir</Option>
                  </Select>
                  <div>
                    <Text strong>
                      Rp{" "}
                      {(
                        item.quantity *
                        (item.priceType === "wholesale"
                          ? item.wholesale
                          : item.price)
                      ).toLocaleString()}
                    </Text>
                  </div>
                </Card>
              ))
            )}
          </div>
          <Title level={5}>Total: Rp {total.toLocaleString()}</Title>
          <Button
            type="primary"
            block
            onClick={() => setCheckoutVisible(true)}
            disabled={cart.length === 0}
          >
            Checkout
          </Button>
        </Sider>
      </Layout>

      {/* Modal Checkout */}
      <Modal
        open={checkoutVisible}
        onCancel={() => setCheckoutVisible(false)}
        footer={null}
        title="Checkout"
      >
        <Form form={form} onFinish={handleCheckout} layout="vertical">
          <Form.Item label="Metode Pembayaran" name="method">
            <Select>
              <Option value="cash">Cash</Option>
              <Option value="transfer">Transfer</Option>
              <Option value="qris">QRIS</Option>
            </Select>
          </Form.Item>
          <Form.Item label="Jumlah Diterima" name="received">
            <InputNumber style={{ width: "100%" }} />
          </Form.Item>
          <Form.Item>
            <Button type="primary" htmlType="submit" block>
              Bayar Rp {total.toLocaleString()}
            </Button>
          </Form.Item>
        </Form>
      </Modal>

      {/* Modal Success */}
      <Modal
        open={successVisible}
        onCancel={() => setSuccessVisible(false)}
        footer={[
          <Button onClick={() => setSuccessVisible(false)}>Tutup</Button>,
        ]}
        title="Sukses"
      >
        <Result
          status="success"
          title="Transaksi Berhasil!"
          subTitle={`Total: Rp ${total.toLocaleString()}`}
        />
      </Modal>
    </Layout>
  );
}
