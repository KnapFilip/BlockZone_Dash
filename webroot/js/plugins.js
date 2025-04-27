document.getElementById("saveChanges").addEventListener("click", () => {
    const rows = document.querySelectorAll("#pluginTable tbody tr");

    let changesSent = 0;
    let errorsOccurred = 0;

    rows.forEach(row => {
        const id = row.getAttribute("data-id");
        const version = row.querySelector(".edit-version").value;
        const status = row.querySelector(".edit-status").value;
        const active = row.querySelector(".edit-active").value === "Ano" ? "ano" : "ne"; // Převod hodnoty na 'ano'/'ne'
        const date = row.querySelector(".edit-date").value;

        // Odeslání dat na server
        fetch("../php/update_plugin.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                id,
                plugin_version: version,
                plugin_status: status,
                plugin_active: active,
                plugin_last_update: date
            })
        })
        .then(res => res.json()) // Očekáváme JSON odpověď
        .then(data => {
            if (data.success) {
                console.log("✅ Plugin " + id + ": Změny úspěšně uloženy.");
                changesSent++; // Počet úspěšně uložených změn
            } else {
                console.error("❌ Chyba u pluginu " + id + ": " + data.error);
                errorsOccurred++; // Počet chyb
            }
        })
        .catch(err => {
            console.error("❌ Chyba u pluginu " + id + ": ", err);
            errorsOccurred++; // Počet chyb
        });
    });

    // Po dokončení odesílání změn
    setTimeout(() => {
        if (changesSent > 0) {
            alert(`✅ Změny byly úspěšně odeslány pro ${changesSent} pluginů.`);
        }

        if (errorsOccurred > 0) {
            alert(`❌ Chyba při odesílání změn pro ${errorsOccurred} pluginů.`);
        }

        if (changesSent === 0 && errorsOccurred === 0) {
            alert("❌ Neprováděly se žádné změny.");
        }
    }, 2000); // Počkej 2 sekundy, než se všechny požadavky zpracují
});
