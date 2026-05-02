FROM node:18.19.0

WORKDIR /app

# Copier les fichiers de dépendances
COPY package*.json ./

# Installer TOUTES les dépendances (y compris devDependencies)
RUN npm install

# Copier le reste du code
COPY . .

EXPOSE 8080

# Lancer le serveur via npm (qui utilisera le binaire local)
CMD ["npm", "run", "serve", "--", "--host", "0.0.0.0"]