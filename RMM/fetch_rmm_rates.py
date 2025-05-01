import sys, requests

# 1️⃣ Nouvelle URL du subgraph Aave V3 Gnosis
URL = "https://gateway.thegraph.com/api/7910f1ec3f636e30d77d9d0a32334eac/subgraphs/id/2xrWGGZ5r8Z7wdNdHxhbRVKcAD2dDgv3F2NcjrZmxifJ"

# 2️⃣ Requête GraphQL ciblant USDC et WXDAI
query = """
query ($assets: [String!]) {
  reserves(where: { underlyingAsset_in: $assets }) {
    symbol
    liquidityRate        # en ray (1e27)
    variableBorrowRate   # en ray
  }
}
"""

# 3️⃣ Adresses "checksummed" des actifs
variables = {
    "assets": [
        "0xDDAfbb505ad214D7b80b1f830fcCc89B60fb7A83",  # USDC
        "0xe91D153E0B41518A2CE8DD3D7944FA863463A97D"   # WXDAI (xDai)
    ]
}

def ray_to_apr(ray: str) -> float:
    """Convertit un taux en ray (1e27) en % APY."""
    #return int(ray) / 1e27 * 100
    apr = int(ray)/1e27         # ex. 0.0644 pour 6.44 %
    seconds = 31_536_000
    apy = (1 + apr/seconds)**seconds - 1
    return apy*100

# 4️⃣ Envoi de la requête et diagnostic
resp = requests.post(URL, json={"query": query, "variables": variables})
print(f"▶ HTTP status: {resp.status_code}")
print("▶ Body preview:", resp.text[:300], "…")
resp.raise_for_status()

result = resp.json()
if "errors" in result:
    print("❌ GraphQL errors:", result["errors"])
    sys.exit(1)
if not result.get("data") or "reserves" not in result["data"]:
    print("❌ Réponse inattendue :", result)
    sys.exit(1)

# 5️⃣ Extraction et stockage des taux
for r in result["data"]["reserves"]:
    liq = ray_to_apr(r["liquidityRate"])
    bor = ray_to_apr(r["variableBorrowRate"])
    
    with open(f'lend_{r["symbol"].lower()}.txt', 'w') as f:
        f.write(f"{int(liq * 100)}")
    with open(f'borrow_{r["symbol"].lower()}.txt', 'w') as f:
        f.write(f"{int(bor * 100)}")
