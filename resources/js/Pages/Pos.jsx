// POSApp.jsx
import React, { useState, useEffect, useRef, useCallback } from "react";
import {
    Layout,
    Input,
    Button,
    Card,
    Badge,
    Modal,
    Row,
    Col,
    Typography,
    Space,
    Divider,
    notification,
    Empty,
    Pagination,
    Select,
    InputNumber,
    Spin,
} from "antd";
import {
    SearchOutlined,
    ShoppingCartOutlined,
    PlusOutlined,
    MinusOutlined,
    DeleteOutlined,
    CheckOutlined,
    PrinterOutlined,
    LeftOutlined,
    RightOutlined,
    ArrowLeftOutlined,
} from "@ant-design/icons";
import axios from "axios";

const { Header, Content, Sider } = Layout;
const { Title, Text } = Typography;
const { Option } = Select;

export default function POSApp({ categories, store, customers }) {
    // PRODUCTS (from API)
    const [products, setProducts] = useState([]);
    const [loading, setLoading] = useState(false);

    // CART
    const [cart, setCart] = useState([]);

    // FILTER / UI
    const [searchText, setSearchText] = useState("");
    const [debouncedSearch, setDebouncedSearch] = useState("");
    const [selectedCategory, setSelectedCategory] = useState("");
    const [currentPage, setCurrentPage] = useState(1);
    const [pageSize] = useState(12);
    const [totalProducts, setTotalProducts] = useState(0);

    // CHECKOUT
    const [checkoutVisible, setCheckoutVisible] = useState(false);
    const [successVisible, setSuccessVisible] = useState(false);
    const [amountReceived, setAmountReceived] = useState(0);
    const [transactionType, setTransactionType] = useState("1");
    const [customer, setCustomer] = useState("");
    const [paymentMethod, setPaymentMethod] = useState("1");
    const [receipt, setReceipt] = useState(null);

    // UI refs
    const categoriesRef = useRef(null);
    const scrollLeft = () =>
        categoriesRef.current?.scrollBy({ left: -200, behavior: "smooth" });
    const scrollRight = () =>
        categoriesRef.current?.scrollBy({ left: 200, behavior: "smooth" });

    // debounce search input (500ms)
    useEffect(() => {
        const t = setTimeout(() => setDebouncedSearch(searchText.trim()), 500);
        return () => clearTimeout(t);
    }, [searchText]);

    // Build stock color
    const getStockColor = (stock) => {
        const n = Number(stock || 0);
        if (n <= 0) return "#b91c1c";
        if (n <= 5) return "#f59e0b";
        return "#16a34a";
    };

    // Calculate total price
    const calculateTotal = useCallback(() => {
        return cart.reduce((sum, item) => {
            const price =
                item.priceType === "regular"
                    ? Number(item.sell_price || 0)
                    : Number(item.sell_retail_price || item.sell_price || 0);
            return sum + item.quantity * price;
        }, 0);
    }, [cart]);

    // Fetch products from Laravel paginate endpoint
    const fetchProducts = useCallback(
        async (page = 1) => {
            try {
                setLoading(true);
                const resp = await axios.get("/pos-inertia/product", {
                    params: {
                        search: debouncedSearch || "",
                        category_id: selectedCategory || "",
                        perPage: pageSize,
                        page,
                    },
                });

                // Laravel paginate() -> resp.data.data, total, current_page
                const data = resp.data;
                const items = data.data || data.items || [];
                setProducts(items);
                setTotalProducts(
                    data.total ?? data.total_items ?? items.length
                );
                setCurrentPage(data.current_page || data.currentPage || page);
            } catch (err) {
                console.error("fetchProducts error", err);
                notification.error({
                    message: "Gagal memuat produk",
                    description:
                        err.response?.data?.message || "Periksa koneksi API",
                    placement: "topRight",
                });
            } finally {
                setLoading(false);
            }
        },
        [debouncedSearch, selectedCategory, pageSize]
    );

    // fetch whenever search/category/currentPage changes
    useEffect(() => {
        fetchProducts(1);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [debouncedSearch, selectedCategory]);

    useEffect(() => {
        fetchProducts(currentPage);
    }, [currentPage, fetchProducts]);

    // Add product to cart and decrement amount_available in products state
    const addToCart = (product) => {
        const stock = Number(product.amount_available || 0);
        if (stock <= 0) {
            notification.warning({
                message: "Stok habis",
                description: `${product.name} tidak tersedia`,
            });
            return;
        }

        // decrement product stock in UI
        setProducts((prev) =>
            prev.map((p) =>
                p.variant_id === product.variant_id
                    ? {
                          ...p,
                          amount_available: Number(p.amount_available || 0) - 1,
                      }
                    : p
            )
        );

        // add or increment cart item (use variant_id as key)
        setCart((prev) => {
            const found = prev.find((i) => i.variant_id === product.variant_id);
            if (found) {
                return prev.map((i) =>
                    i.variant_id === product.variant_id
                        ? { ...i, quantity: i.quantity + 1 }
                        : i
                );
            } else {
                return [
                    ...prev,
                    {
                        ...product,
                        quantity: 1,
                        priceType: "regular", // default price type
                    },
                ];
            }
        });
    };

    // Update quantity (delta = +1 or -1)
    const updateQuantity = (variant_id, delta) => {
        setCart((prev) => {
            return prev.flatMap((item) => {
                if (item.variant_id !== variant_id) return item;

                const newQty = item.quantity + delta;
                if (delta > 0) {
                    // check stock in products state
                    const prod = products.find(
                        (p) => p.variant_id === variant_id
                    );
                    if (!prod || Number(prod.amount_available || 0) <= 0) {
                        notification.warning({
                            message: "Stok tidak mencukupi",
                        });
                        return item;
                    }
                    // decrement product stock
                    setProducts((prevP) =>
                        prevP.map((p) =>
                            p.variant_id === variant_id
                                ? {
                                      ...p,
                                      amount_available:
                                          Number(p.amount_available || 0) - 1,
                                  }
                                : p
                        )
                    );
                    return { ...item, quantity: newQty };
                } else {
                    // restore stock
                    setProducts((prevP) =>
                        prevP.map((p) =>
                            p.variant_id === variant_id
                                ? {
                                      ...p,
                                      amount_available:
                                          Number(p.amount_available || 0) + 1,
                                  }
                                : p
                        )
                    );
                    if (newQty <= 0) return []; // remove item
                    return { ...item, quantity: newQty };
                }
            });
        });
    };

    // Remove item from cart and restore stock
    const removeFromCart = (variant_id) => {
        setCart((prev) => {
            const item = prev.find((i) => i.variant_id === variant_id);
            if (!item) return prev;

            // restore stock in products
            setProducts((prevP) =>
                prevP.map((p) =>
                    p.variant_id === variant_id
                        ? {
                              ...p,
                              amount_available:
                                  Number(p.amount_available || 0) +
                                  item.quantity,
                          }
                        : p
                )
            );

            return prev.filter((i) => i.variant_id !== variant_id);
        });
    };

    // Change price type (regular / wholesale)
    const changePriceType = (variant_id, priceType) => {
        setCart((prev) =>
            prev.map((i) =>
                i.variant_id === variant_id ? { ...i, priceType } : i
            )
        );
    };

    // Process checkout (local only). You likely want to POST this to backend in production.
    const processCheckout = async () => {
        const total = calculateTotal();

        if (transactionType === "1" && Number(amountReceived || 0) < total) {
            notification.error({
                message: "Pembayaran gagal",
                description: "Jumlah uang diterima kurang dari total belanja",
            });
            return;
        }

        const payload = {
            transaction_type: transactionType, // 1=Tunai, 2=Kasbon
            payment_method_id: Number(paymentMethod),
            customer_id: customer || null,
            amount_received: Number(amountReceived || 0),
            list_product: cart.map((item) => ({
                product_id: item.product_id,
                variant_id: item.variant_id,
                ingredient_id: null,
                name: item.name,
                active_price:
                    item.priceType === "regular"
                        ? Number(item.sell_price)
                        : Number(item.sell_retail_price || item.sell_price),
                amount: item.quantity,
                buy_price: Number(item.buy_price || 0),
                sell_price: Number(item.sell_price || 0),
            })),
        };

        try {
            setLoading(true);
            const resp = await axios.post(
                "/pos-inertia/submit-payment",
                payload
            );
            console.log("processCheckout response", resp.data);

            if (resp.data.success) {
                notification.success({
                    message: "Transaksi Berhasil",
                    description: resp.data.message,
                });
                setCheckoutVisible(false);
                setSuccessVisible(true);
                setReceipt(resp.data.data || null);
                setCart([]);
            } else {
                notification.error({
                    message: "Gagal",
                    description: resp.data.message,
                });
            }
        } catch (err) {
            notification.error({
                message: "Error",
                description:
                    err.response?.data?.message ||
                    "Terjadi kesalahan pada server",
            });
        } finally {
            setLoading(false);
        }
    };

    const newTransaction = () => {
        // clear cart; stocks remain decreased (assuming transaction finalized)
        setCart([]);
        setAmountReceived(0);
        setTransactionType("Tunai Langsung");
        setCustomer("Umum");
        setPaymentMethod("Cash");
        setSuccessVisible(false);
    };

    // Render
    return (
        <Layout style={{ minHeight: "100vh" }}>
            {/* Header */}
            <Header
                style={{
                    background:
                        "linear-gradient(135deg,#0891b2 0%,#059669 100%)",
                    padding: "0 20px",
                    display: "flex",
                    alignItems: "center",
                    justifyContent: "space-between",
                }}
            >
                <Space>
                    <Button
                        type="default"
                        icon={<ArrowLeftOutlined />}
                        onClick={() =>
                            (window.location.href = "/admin/dashboard")
                        }
                    >
                        Kembali
                    </Button>

                    <div>
                        <Title level={3} style={{ color: "white", margin: 0 }}>
                            {store}
                        </Title>
                    </div>
                </Space>

                <Space>
                    <Button
                        type="text"
                        style={{ color: "white" }}
                        icon={<ShoppingCartOutlined />}
                    />
                </Space>
            </Header>

            <Layout>
                <Content style={{ padding: 20 }}>
                    <Space
                        direction="vertical"
                        style={{ width: "100%" }}
                        size="large"
                    >
                        {/* Search */}
                        <Input
                            size="large"
                            placeholder="Cari produk (nama atau kode)..."
                            prefix={<SearchOutlined />}
                            value={searchText}
                            onChange={(e) => {
                                setSearchText(e.target.value);
                                setCurrentPage(1);
                            }}
                        />

                        {/* Categories */}
                        <div
                            style={{
                                display: "flex",
                                alignItems: "center",
                                gap: 8,
                            }}
                        >
                            <Button
                                icon={<LeftOutlined />}
                                onClick={scrollLeft}
                            />
                            <div
                                ref={categoriesRef}
                                style={{
                                    display: "flex",
                                    gap: 12,
                                    overflowX: "auto",
                                    whiteSpace: "nowrap",
                                    flex: 1,
                                }}
                            >
                                <Button
                                    type={
                                        !selectedCategory
                                            ? "primary"
                                            : "default"
                                    }
                                    onClick={() => {
                                        setSelectedCategory("");
                                        setCurrentPage(1);
                                    }}
                                >
                                    🏪 Semua
                                </Button>

                                {categories.map((cat) => (
                                    <Button
                                        key={cat.id}
                                        type={
                                            selectedCategory === cat.id
                                                ? "primary"
                                                : "default"
                                        }
                                        onClick={() => {
                                            setSelectedCategory(cat.id);
                                            setCurrentPage(1);
                                        }}
                                    >
                                        <div
                                            style={{
                                                display: "flex",
                                                flexDirection: "column",
                                                alignItems: "center",
                                            }}
                                        >
                                            <small>{cat.name}</small>
                                        </div>
                                    </Button>
                                ))}
                            </div>
                            <Button
                                icon={<RightOutlined />}
                                onClick={scrollRight}
                            />
                        </div>

                        {/* Product Grid */}
                        {loading ? (
                            <div style={{ textAlign: "center", padding: 40 }}>
                                <Spin size="large" />
                            </div>
                        ) : products.length === 0 ? (
                            <div style={{ textAlign: "center", padding: 60 }}>
                                <Empty description="Produk tidak ditemukan" />
                            </div>
                        ) : (
                            <>
                                <Row gutter={[16, 16]}>
                                    {products.map((product) => (
                                        <Col
                                            key={product.variant_id}
                                            xs={12}
                                            sm={8}
                                            md={6}
                                        >
                                            <Card
                                                hoverable
                                                bodyStyle={{ padding: 14 }}
                                            >
                                                <Space
                                                    direction="vertical"
                                                    size="small"
                                                    style={{ width: "100%" }}
                                                >
                                                    <div
                                                        style={{
                                                            textAlign: "center",
                                                            height: 84,
                                                        }}
                                                    >
                                                        {product.image ? (
                                                            <img
                                                                src={
                                                                    product.image.startsWith(
                                                                        "http"
                                                                    )
                                                                        ? product.image
                                                                        : `/storage/${product.image}`
                                                                }
                                                                alt={
                                                                    product.name
                                                                }
                                                                style={{
                                                                    maxHeight: 76,
                                                                    objectFit:
                                                                        "contain",
                                                                    borderRadius: 6,
                                                                }}
                                                                onError={(
                                                                    e
                                                                ) => {
                                                                    e.target.onerror =
                                                                        null;
                                                                }}
                                                            />
                                                        ) : (
                                                            <div
                                                                style={{
                                                                    height: 76,
                                                                    background:
                                                                        "#f3f4f6",
                                                                    display:
                                                                        "flex",
                                                                    alignItems:
                                                                        "center",
                                                                    justifyContent:
                                                                        "center",
                                                                    color: "#9ca3af",
                                                                }}
                                                            >
                                                                No Image
                                                            </div>
                                                        )}
                                                    </div>

                                                    <Text
                                                        strong
                                                        style={{
                                                            display: "block",
                                                            textAlign: "center",
                                                        }}
                                                        ellipsis={{
                                                            tooltip:
                                                                product.name,
                                                        }}
                                                    >
                                                        {product.name}
                                                    </Text>

                                                    <div
                                                        style={{
                                                            display: "flex",
                                                            justifyContent:
                                                                "center",
                                                        }}
                                                    >
                                                        <Badge
                                                            count={`Stok: ${
                                                                product.amount_available ??
                                                                0
                                                            }`}
                                                            style={{
                                                                backgroundColor:
                                                                    getStockColor(
                                                                        Number(
                                                                            product.amount_available ||
                                                                                0
                                                                        )
                                                                    ),
                                                            }}
                                                        />
                                                    </div>

                                                    <Divider
                                                        style={{
                                                            margin: "8px 0",
                                                        }}
                                                    />

                                                    <div
                                                        style={{
                                                            textAlign: "center",
                                                        }}
                                                    >
                                                        <Text
                                                            type="secondary"
                                                            style={{
                                                                fontSize: 12,
                                                            }}
                                                        >
                                                            Harga
                                                        </Text>
                                                        <div>
                                                            <Text
                                                                strong
                                                                style={{
                                                                    color: "#10b981",
                                                                }}
                                                            >
                                                                Rp{" "}
                                                                {Number(
                                                                    product.sell_price ||
                                                                        0
                                                                ).toLocaleString()}
                                                            </Text>
                                                        </div>
                                                        {product.unit_name && (
                                                            <div>
                                                                <Text
                                                                    type="secondary"
                                                                    style={{
                                                                        fontSize: 11,
                                                                    }}
                                                                >
                                                                    {
                                                                        product.unit_name
                                                                    }
                                                                </Text>
                                                            </div>
                                                        )}
                                                    </div>

                                                    <Button
                                                        block
                                                        type="primary"
                                                        icon={<PlusOutlined />}
                                                        onClick={() =>
                                                            addToCart(product)
                                                        }
                                                        disabled={
                                                            Number(
                                                                product.amount_available ||
                                                                    0
                                                            ) <= 0
                                                        }
                                                    >
                                                        {Number(
                                                            product.amount_available ||
                                                                0
                                                        ) <= 0
                                                            ? "Habis"
                                                            : "Tambah"}
                                                    </Button>
                                                </Space>
                                            </Card>
                                        </Col>
                                    ))}
                                </Row>

                                <div
                                    style={{
                                        textAlign: "center",
                                        marginTop: 14,
                                    }}
                                >
                                    <Pagination
                                        current={currentPage}
                                        pageSize={pageSize}
                                        total={totalProducts}
                                        onChange={(page) =>
                                            setCurrentPage(page)
                                        }
                                        showSizeChanger={false}
                                    />
                                </div>
                            </>
                        )}
                    </Space>
                </Content>

                {/* Cart Sider */}
                <Sider
                    width={380}
                    style={{
                        background: "white",
                        borderLeft: "1px solid #f0f0f0",
                        padding: 20,
                    }}
                >
                    <Title level={4}>
                        <ShoppingCartOutlined /> Keranjang
                    </Title>
                    <Divider />
                    {cart.length === 0 ? (
                        <Empty description="Keranjang kosong" />
                    ) : (
                        <div
                            style={{
                                display: "flex",
                                flexDirection: "column",
                                gap: 12,
                                maxHeight: "60vh",
                                overflowY: "auto",
                            }}
                        >
                            {cart.map((item) => (
                                <Card key={item.variant_id} size="small">
                                    <Space
                                        direction="vertical"
                                        style={{ width: "100%" }}
                                    >
                                        <div
                                            style={{
                                                display: "flex",
                                                justifyContent: "space-between",
                                                alignItems: "center",
                                            }}
                                        >
                                            <Text
                                                strong
                                                style={{ fontSize: 13 }}
                                            >
                                                {item.name}
                                            </Text>
                                            <Button
                                                type="text"
                                                danger
                                                icon={<DeleteOutlined />}
                                                onClick={() =>
                                                    removeFromCart(
                                                        item.variant_id
                                                    )
                                                }
                                            />
                                        </div>

                                        <div
                                            style={{
                                                display: "flex",
                                                justifyContent: "space-between",
                                                alignItems: "center",
                                            }}
                                        >
                                            <Space>
                                                <Button
                                                    size="small"
                                                    onClick={() =>
                                                        updateQuantity(
                                                            item.variant_id,
                                                            -1
                                                        )
                                                    }
                                                    icon={<MinusOutlined />}
                                                />
                                                <Text>{item.quantity}</Text>
                                                <Button
                                                    size="small"
                                                    onClick={() =>
                                                        updateQuantity(
                                                            item.variant_id,
                                                            1
                                                        )
                                                    }
                                                    icon={<PlusOutlined />}
                                                />
                                            </Space>

                                            <Select
                                                size="small"
                                                value={item.priceType}
                                                onChange={(v) =>
                                                    changePriceType(
                                                        item.variant_id,
                                                        v
                                                    )
                                                }
                                                style={{ width: 110 }}
                                            >
                                                <Option value="regular">
                                                    Reguler
                                                </Option>
                                                <Option value="wholesale">
                                                    Grosir
                                                </Option>
                                            </Select>
                                        </div>

                                        <div style={{ textAlign: "right" }}>
                                            <Text
                                                strong
                                                style={{ color: "#10b981" }}
                                            >
                                                Rp{" "}
                                                {(
                                                    item.quantity *
                                                    (item.priceType ===
                                                    "regular"
                                                        ? Number(
                                                              item.sell_price ||
                                                                  0
                                                          )
                                                        : Number(
                                                              item.sell_retail_price ||
                                                                  item.sell_price ||
                                                                  0
                                                          ))
                                                ).toLocaleString()}
                                            </Text>
                                        </div>
                                    </Space>
                                </Card>
                            ))}
                        </div>
                    )}

                    <Divider />
                    <div
                        style={{
                            display: "flex",
                            justifyContent: "space-between",
                            alignItems: "center",
                        }}
                    >
                        <Text strong>Total:</Text>
                        <Title
                            level={3}
                            style={{ margin: 0, color: "#10b981" }}
                        >
                            Rp {calculateTotal().toLocaleString()}
                        </Title>
                    </div>

                    <Button
                        block
                        type="primary"
                        icon={<CheckOutlined />}
                        size="large"
                        style={{ marginTop: 12 }}
                        disabled={cart.length === 0}
                        onClick={() => setCheckoutVisible(true)}
                    >
                        Checkout
                    </Button>
                </Sider>
            </Layout>

            {/* Checkout Modal */}
            <Modal
                title="Checkout"
                open={checkoutVisible}
                onCancel={() => setCheckoutVisible(false)}
                footer={[
                    <Button
                        key="cancel"
                        onClick={() => setCheckoutVisible(false)}
                    >
                        Batal
                    </Button>,
                    <Button
                        key="submit"
                        type="primary"
                        onClick={processCheckout}
                    >
                        Bayar & Selesai
                    </Button>,
                ]}
                width={560}
            >
                <Space
                    direction="vertical"
                    style={{ width: "100%" }}
                    size="middle"
                >
                    <div>
                        <Text strong>Jenis Transaksi</Text>
                        <Select
                            value={transactionType}
                            onChange={setTransactionType}
                            style={{ width: "100%", marginTop: 6 }}
                        >
                            <Option value="1">Penjualan</Option>
                            <Option value="2">Kasbon</Option>
                        </Select>
                    </div>

                    <div>
                        <Text strong>Pilih Pelanggan</Text>
                        <Select
                            value={customer}
                            onChange={setCustomer}
                            style={{ width: "100%", marginTop: 6 }}
                        >
                            <Option key={0} value={""}>
                                Pilih Customer
                            </Option>
                            {customers.map((e, i) => (
                                <Option key={i} value={e.id}>
                                    {e.name}
                                </Option>
                            ))}
                        </Select>
                    </div>

                    <div>
                        <Text strong>Metode Pembayaran</Text>
                        <Select
                            value={paymentMethod}
                            onChange={setPaymentMethod}
                            style={{ width: "100%", marginTop: 6 }}
                        >
                            <Option value="1">Tunai</Option>
                        </Select>
                    </div>

                    <div>
                        <Text strong>Jumlah Uang Diterima</Text>
                        <InputNumber
                            style={{ width: "100%", marginTop: 6 }}
                            formatter={(v) =>
                                `Rp ${v}`.replace(/\B(?=(\d{3})+(?!\d))/g, ",")
                            }
                            parser={(v) => v.replace(/Rp\s?|(,*)/g, "")}
                            onChange={(v) => setAmountReceived(Number(v) || 0)}
                        />
                    </div>

                    <Card style={{ background: "#fafafa" }}>
                        <div
                            style={{
                                display: "flex",
                                justifyContent: "space-between",
                            }}
                        >
                            <Text>Total Belanja:</Text>
                            <Text strong style={{ color: "#10b981" }}>
                                Rp {calculateTotal().toLocaleString()}
                            </Text>
                        </div>
                        <div
                            style={{
                                display: "flex",
                                justifyContent: "space-between",
                            }}
                        >
                            <Text>Kembalian:</Text>
                            <Text strong style={{ color: "#3b82f6" }}>
                                Rp{" "}
                                {Math.max(
                                    0,
                                    (amountReceived || 0) - calculateTotal()
                                ).toLocaleString()}
                            </Text>
                        </div>
                    </Card>
                </Space>
            </Modal>

            {/* Success Modal */}
            <Modal
                title="Transaksi Berhasil"
                open={successVisible}
                onCancel={() => setSuccessVisible(false)}
                footer={[
                    <Button
                        key="print"
                        icon={<PrinterOutlined />}
                        onClick={() => {
                            if (window.ReactNativeWebView) {
                                window.ReactNativeWebView.postMessage(
                                    JSON.stringify({
                                        type: "print",
                                        payload: receipt,
                                    })
                                );
                            } else {
                                console.log("Printing not supported outside native app.");
                                notification.info({
                                    message:
                                        "Cetak hanya didukung di aplikasi native.",
                                });
                            }
                        }}
                    >
                        Cetak Struk
                    </Button>,
                    <Button key="new" type="primary" onClick={newTransaction}>
                        Transaksi Baru
                    </Button>,
                ]}
            >
                <div style={{ textAlign: "center" }}>
                    <div
                        style={{
                            width: 72,
                            height: 72,
                            borderRadius: 36,
                            background: "#d1fae5",
                            margin: "0 auto 12px",
                            display: "flex",
                            alignItems: "center",
                            justifyContent: "center",
                            fontSize: 36,
                        }}
                    >
                        ✅
                    </div>
                    <Title level={4}>Transaksi Berhasil!</Title>
                    <Divider />
                    <div
                        style={{
                            display: "flex",
                            justifyContent: "space-between",
                        }}
                    >
                        <Text>Total Item:</Text>
                        <Text strong>
                            {cart.reduce((s, i) => s + i.quantity, 0)}
                        </Text>
                    </div>
                    <div
                        style={{
                            display: "flex",
                            justifyContent: "space-between",
                        }}
                    >
                        <Text>Total Harga:</Text>
                        <Text strong>
                            Rp {calculateTotal().toLocaleString()}
                        </Text>
                    </div>
                    <div
                        style={{
                            display: "flex",
                            justifyContent: "space-between",
                        }}
                    >
                        <Text>Metode Bayar:</Text>
                        <Text strong>{paymentMethod}</Text>
                    </div>
                </div>
            </Modal>
        </Layout>
    );
}
